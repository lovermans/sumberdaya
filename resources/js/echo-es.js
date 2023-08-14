class Channel {
    listenForWhisper(event, callback) {
        return this.listen('.client-' + event, callback);
    }
    notification(callback) {
        return this.listen('.Illuminate\\Notifications\\Events\\BroadcastNotificationCreated', callback);
    }
    stopListeningForWhisper(event, callback) {
        return this.stopListening('.client-' + event, callback);
    }
}

class EventFormatter {
    constructor(namespace) {
        this.namespace = namespace;
    }
    format(event) {
        if (event.charAt(0) === '.' || event.charAt(0) === '\\') {
            return event.substr(1);
        }
        else if (this.namespace) {
            event = this.namespace + '.' + event;
        }
        return event.replace(/\./g, '\\');
    }
    setNamespace(value) {
        this.namespace = value;
    }
}

class PusherChannel extends Channel {
    constructor(pusher, name, options) {
        super();
        this.name = name;
        this.pusher = pusher;
        this.options = options;
        this.eventFormatter = new EventFormatter(this.options.namespace);
        this.subscribe();
    }
    subscribe() {
        this.subscription = this.pusher.subscribe(this.name);
    }
    unsubscribe() {
        this.pusher.unsubscribe(this.name);
    }
    listen(event, callback) {
        this.on(this.eventFormatter.format(event), callback);
        return this;
    }
    listenToAll(callback) {
        this.subscription.bind_global((event, data) => {
            if (event.startsWith('pusher:')) {
                return;
            }
            let namespace = this.options.namespace.replace(/\./g, '\\');
            let formattedEvent = event.startsWith(namespace) ? event.substring(namespace.length + 1) : '.' + event;
            callback(formattedEvent, data);
        });
        return this;
    }
    stopListening(event, callback) {
        if (callback) {
            this.subscription.unbind(this.eventFormatter.format(event), callback);
        }
        else {
            this.subscription.unbind(this.eventFormatter.format(event));
        }
        return this;
    }
    stopListeningToAll(callback) {
        if (callback) {
            this.subscription.unbind_global(callback);
        }
        else {
            this.subscription.unbind_global();
        }
        return this;
    }
    subscribed(callback) {
        this.on('pusher:subscription_succeeded', () => {
            callback();
        });
        return this;
    }
    error(callback) {
        this.on('pusher:subscription_error', (status) => {
            callback(status);
        });
        return this;
    }
    on(event, callback) {
        this.subscription.bind(event, callback);
        return this;
    }
}

class PusherPrivateChannel extends PusherChannel {
    whisper(eventName, data) {
        this.pusher.channels.channels[this.name].trigger(`client-${eventName}`, data);
        return this;
    }
}

class PusherEncryptedPrivateChannel extends PusherChannel {
    whisper(eventName, data) {
        this.pusher.channels.channels[this.name].trigger(`client-${eventName}`, data);
        return this;
    }
}

class PusherPresenceChannel extends PusherChannel {
    here(callback) {
        this.on('pusher:subscription_succeeded', (data) => {
            callback(Object.keys(data.members).map((k) => data.members[k]));
        });
        return this;
    }
    joining(callback) {
        this.on('pusher:member_added', (member) => {
            callback(member.info);
        });
        return this;
    }
    whisper(eventName, data) {
        this.pusher.channels.channels[this.name].trigger(`client-${eventName}`, data);
        return this;
    }
    leaving(callback) {
        this.on('pusher:member_removed', (member) => {
            callback(member.info);
        });
        return this;
    }
}

class SocketIoChannel extends Channel {
    constructor(socket, name, options) {
        super();
        this.events = {};
        this.listeners = {};
        this.name = name;
        this.socket = socket;
        this.options = options;
        this.eventFormatter = new EventFormatter(this.options.namespace);
        this.subscribe();
    }
    subscribe() {
        this.socket.emit('subscribe', {
            channel: this.name,
            auth: this.options.auth || {},
        });
    }
    unsubscribe() {
        this.unbind();
        this.socket.emit('unsubscribe', {
            channel: this.name,
            auth: this.options.auth || {},
        });
    }
    listen(event, callback) {
        this.on(this.eventFormatter.format(event), callback);
        return this;
    }
    stopListening(event, callback) {
        this.unbindEvent(this.eventFormatter.format(event), callback);
        return this;
    }
    subscribed(callback) {
        this.on('connect', (socket) => {
            callback(socket);
        });
        return this;
    }
    error(callback) {
        return this;
    }
    on(event, callback) {
        this.listeners[event] = this.listeners[event] || [];
        if (!this.events[event]) {
            this.events[event] = (channel, data) => {
                if (this.name === channel && this.listeners[event]) {
                    this.listeners[event].forEach((cb) => cb(data));
                }
            };
            this.socket.on(event, this.events[event]);
        }
        this.listeners[event].push(callback);
        return this;
    }
    unbind() {
        Object.keys(this.events).forEach((event) => {
            this.unbindEvent(event);
        });
    }
    unbindEvent(event, callback) {
        this.listeners[event] = this.listeners[event] || [];
        if (callback) {
            this.listeners[event] = this.listeners[event].filter((cb) => cb !== callback);
        }
        if (!callback || this.listeners[event].length === 0) {
            if (this.events[event]) {
                this.socket.removeListener(event, this.events[event]);
                delete this.events[event];
            }
            delete this.listeners[event];
        }
    }
}

class SocketIoPrivateChannel extends SocketIoChannel {
    whisper(eventName, data) {
        this.socket.emit('client event', {
            channel: this.name,
            event: `client-${eventName}`,
            data: data,
        });
        return this;
    }
}

class SocketIoPresenceChannel extends SocketIoPrivateChannel {
    here(callback) {
        this.on('presence:subscribed', (members) => {
            callback(members.map((m) => m.user_info));
        });
        return this;
    }
    joining(callback) {
        this.on('presence:joining', (member) => callback(member.user_info));
        return this;
    }
    whisper(eventName, data) {
        this.socket.emit('client event', {
            channel: this.name,
            event: `client-${eventName}`,
            data: data,
        });
        return this;
    }
    leaving(callback) {
        this.on('presence:leaving', (member) => callback(member.user_info));
        return this;
    }
}

class NullChannel extends Channel {
    subscribe() {
    }
    unsubscribe() {
    }
    listen(event, callback) {
        return this;
    }
    listenToAll(callback) {
        return this;
    }
    stopListening(event, callback) {
        return this;
    }
    subscribed(callback) {
        return this;
    }
    error(callback) {
        return this;
    }
    on(event, callback) {
        return this;
    }
}

class NullPrivateChannel extends NullChannel {
    whisper(eventName, data) {
        return this;
    }
}

class NullPresenceChannel extends NullChannel {
    here(callback) {
        return this;
    }
    joining(callback) {
        return this;
    }
    whisper(eventName, data) {
        return this;
    }
    leaving(callback) {
        return this;
    }
}

class Connector {
    constructor(options) {
        this._defaultOptions = {
            auth: {
                headers: {},
            },
            authEndpoint: '/broadcasting/auth',
            userAuthentication: {
                endpoint: '/broadcasting/user-auth',
                headers: {},
            },
            broadcaster: 'pusher',
            csrfToken: null,
            bearerToken: null,
            host: null,
            key: null,
            namespace: 'App.Events',
        };
        this.setOptions(options);
        this.connect();
    }
    setOptions(options) {
        this.options = Object.assign(this._defaultOptions, options);
        let token = this.csrfToken();
        if (token) {
            this.options.auth.headers['X-CSRF-TOKEN'] = token;
            this.options.userAuthentication.headers['X-CSRF-TOKEN'] = token;
        }
        token = this.options.bearerToken;
        if (token) {
            this.options.auth.headers['Authorization'] = 'Bearer ' + token;
            this.options.userAuthentication.headers['Authorization'] = 'Bearer ' + token;
        }
        return options;
    }
    csrfToken() {
        let selector;
        if (typeof window !== 'undefined' && window['Laravel'] && window['Laravel'].csrfToken) {
            return window['Laravel'].csrfToken;
        }
        else if (this.options.csrfToken) {
            return this.options.csrfToken;
        }
        else if (typeof document !== 'undefined' &&
            typeof document.querySelector === 'function' &&
            (selector = document.querySelector('meta[name="csrf-token"]'))) {
            return selector.getAttribute('content');
        }
        return null;
    }
}

class PusherConnector extends Connector {
    constructor() {
        super(...arguments);
        this.channels = {};
    }
    connect() {
        if (typeof this.options.client !== 'undefined') {
            this.pusher = this.options.client;
        }
        else if (this.options.Pusher) {
            this.pusher = new this.options.Pusher(this.options.key, this.options);
        }
        else {
            this.pusher = new Pusher(this.options.key, this.options);
        }
    }
    signin() {
        this.pusher.signin();
    }
    listen(name, event, callback) {
        return this.channel(name).listen(event, callback);
    }
    channel(name) {
        if (!this.channels[name]) {
            this.channels[name] = new PusherChannel(this.pusher, name, this.options);
        }
        return this.channels[name];
    }
    privateChannel(name) {
        if (!this.channels['private-' + name]) {
            this.channels['private-' + name] = new PusherPrivateChannel(this.pusher, 'private-' + name, this.options);
        }
        return this.channels['private-' + name];
    }
    encryptedPrivateChannel(name) {
        if (!this.channels['private-encrypted-' + name]) {
            this.channels['private-encrypted-' + name] = new PusherEncryptedPrivateChannel(this.pusher, 'private-encrypted-' + name, this.options);
        }
        return this.channels['private-encrypted-' + name];
    }
    presenceChannel(name) {
        if (!this.channels['presence-' + name]) {
            this.channels['presence-' + name] = new PusherPresenceChannel(this.pusher, 'presence-' + name, this.options);
        }
        return this.channels['presence-' + name];
    }
    leave(name) {
        let channels = [name, 'private-' + name, 'private-encrypted-' + name, 'presence-' + name];
        channels.forEach((name, index) => {
            this.leaveChannel(name);
        });
    }
    leaveChannel(name) {
        if (this.channels[name]) {
            this.channels[name].unsubscribe();
            delete this.channels[name];
        }
    }
    socketId() {
        return this.pusher.connection.socket_id;
    }
    disconnect() {
        this.pusher.disconnect();
    }
}

class SocketIoConnector extends Connector {
    constructor() {
        super(...arguments);
        this.channels = {};
    }
    connect() {
        let io = this.getSocketIO();
        this.socket = io(this.options.host, this.options);
        this.socket.on('reconnect', () => {
            Object.values(this.channels).forEach((channel) => {
                channel.subscribe();
            });
        });
        return this.socket;
    }
    getSocketIO() {
        if (typeof this.options.client !== 'undefined') {
            return this.options.client;
        }
        if (typeof io !== 'undefined') {
            return io;
        }
        throw new Error('Socket.io client not found. Should be globally available or passed via options.client');
    }
    listen(name, event, callback) {
        return this.channel(name).listen(event, callback);
    }
    channel(name) {
        if (!this.channels[name]) {
            this.channels[name] = new SocketIoChannel(this.socket, name, this.options);
        }
        return this.channels[name];
    }
    privateChannel(name) {
        if (!this.channels['private-' + name]) {
            this.channels['private-' + name] = new SocketIoPrivateChannel(this.socket, 'private-' + name, this.options);
        }
        return this.channels['private-' + name];
    }
    presenceChannel(name) {
        if (!this.channels['presence-' + name]) {
            this.channels['presence-' + name] = new SocketIoPresenceChannel(this.socket, 'presence-' + name, this.options);
        }
        return this.channels['presence-' + name];
    }
    leave(name) {
        let channels = [name, 'private-' + name, 'presence-' + name];
        channels.forEach((name) => {
            this.leaveChannel(name);
        });
    }
    leaveChannel(name) {
        if (this.channels[name]) {
            this.channels[name].unsubscribe();
            delete this.channels[name];
        }
    }
    socketId() {
        return this.socket.id;
    }
    disconnect() {
        this.socket.disconnect();
    }
}

class NullConnector extends Connector {
    constructor() {
        super(...arguments);
        this.channels = {};
    }
    connect() {
    }
    listen(name, event, callback) {
        return new NullChannel();
    }
    channel(name) {
        return new NullChannel();
    }
    privateChannel(name) {
        return new NullPrivateChannel();
    }
    encryptedPrivateChannel(name) {
        return new NullPrivateChannel();
    }
    presenceChannel(name) {
        return new NullPresenceChannel();
    }
    leave(name) {
    }
    leaveChannel(name) {
    }
    socketId() {
        return 'fake-socket-id';
    }
    disconnect() {
    }
}

class Echo {
    constructor(options) {
        this.options = options;
        this.connect();
        if (!this.options.withoutInterceptors) {
            this.registerInterceptors();
        }
    }
    channel(channel) {
        return this.connector.channel(channel);
    }
    connect() {
        if (this.options.broadcaster == 'pusher') {
            this.connector = new PusherConnector(this.options);
        }
        else if (this.options.broadcaster == 'socket.io') {
            this.connector = new SocketIoConnector(this.options);
        }
        else if (this.options.broadcaster == 'null') {
            this.connector = new NullConnector(this.options);
        }
        else if (typeof this.options.broadcaster == 'function') {
            this.connector = new this.options.broadcaster(this.options);
        }
    }
    disconnect() {
        this.connector.disconnect();
    }
    join(channel) {
        return this.connector.presenceChannel(channel);
    }
    leave(channel) {
        this.connector.leave(channel);
    }
    leaveChannel(channel) {
        this.connector.leaveChannel(channel);
    }
    leaveAllChannels() {
        for (const channel in this.connector.channels) {
            this.leaveChannel(channel);
        }
    }
    listen(channel, event, callback) {
        return this.connector.listen(channel, event, callback);
    }
    private(channel) {
        return this.connector.privateChannel(channel);
    }
    encryptedPrivate(channel) {
        return this.connector.encryptedPrivateChannel(channel);
    }
    socketId() {
        return this.connector.socketId();
    }
    registerInterceptors() {
        if (typeof Vue === 'function' && Vue.http) {
            this.registerVueRequestInterceptor();
        }
        if (typeof axios === 'function') {
            this.registerAxiosRequestInterceptor();
        }
        if (typeof jQuery === 'function') {
            this.registerjQueryAjaxSetup();
        }
        if (typeof Turbo === 'object') {
            this.registerTurboRequestInterceptor();
        }
    }
    registerVueRequestInterceptor() {
        Vue.http.interceptors.push((request, next) => {
            if (this.socketId()) {
                request.headers.set('X-Socket-ID', this.socketId());
            }
            next();
        });
    }
    registerAxiosRequestInterceptor() {
        axios.interceptors.request.use((config) => {
            if (this.socketId()) {
                config.headers['X-Socket-Id'] = this.socketId();
            }
            return config;
        });
    }
    registerjQueryAjaxSetup() {
        if (typeof jQuery.ajax != 'undefined') {
            jQuery.ajaxPrefilter((options, originalOptions, xhr) => {
                if (this.socketId()) {
                    xhr.setRequestHeader('X-Socket-Id', this.socketId());
                }
            });
        }
    }
    registerTurboRequestInterceptor() {
        document.addEventListener('turbo:before-fetch-request', (event) => {
            event.detail.fetchOptions.headers['X-Socket-Id'] = this.socketId();
        });
    }
}

export { Echo as default };
