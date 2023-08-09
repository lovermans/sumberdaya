class Config {
    constructor(info) {
        this.id = '';
        this.isMultiple = false;
        this.isAjax = false;
        this.isSearching = false;
        this.showSearch = true;
        this.searchFocus = true;
        this.searchHighlight = false;
        this.closeOnSelect = true;
        this.showContent = 'auto';
        this.searchPlaceholder = 'Search';
        this.searchText = 'No Results';
        this.searchingText = 'Searching...';
        this.placeholderText = 'Select Value';
        this.allowDeselect = false;
        this.allowDeselectOption = false;
        this.hideSelectedOption = false;
        this.deselectLabel = 'x';
        this.isEnabled = true;
        this.valuesUseText = false;
        this.showOptionTooltips = false;
        this.selectByGroup = false;
        this.limit = 0;
        this.timeoutDelay = 200;
        this.addToBody = false;
        this.main = 'ss-main';
        this.singleSelected = 'ss-single-selected';
        this.arrow = 'ss-arrow';
        this.multiSelected = 'ss-multi-selected';
        this.add = 'ss-add';
        this.plus = 'ss-plus';
        this.values = 'ss-values';
        this.value = 'ss-value';
        this.valueText = 'ss-value-text';
        this.valueDelete = 'ss-value-delete';
        this.content = 'ss-content';
        this.open = 'ss-open';
        this.openAbove = 'ss-open-above';
        this.openBelow = 'ss-open-below';
        this.search = 'ss-search';
        this.searchHighlighter = 'ss-search-highlight';
        this.addable = 'ss-addable';
        this.list = 'ss-list';
        this.optgroup = 'ss-optgroup';
        this.optgroupLabel = 'ss-optgroup-label';
        this.optgroupLabelSelectable = 'ss-optgroup-label-selectable';
        this.option = 'ss-option';
        this.optionSelected = 'ss-option-selected';
        this.highlighted = 'ss-highlighted';
        this.disabled = 'ss-disabled';
        this.hide = 'ss-hide';
        this.id = 'ss-' + Math.floor(Math.random() * 100000);
        this.style = info.select.style.cssText;
        this.class = info.select.className.split(' ');
        this.isMultiple = info.select.multiple;
        this.isAjax = info.isAjax;
        this.showSearch = (info.showSearch === false ? false : true);
        this.searchFocus = (info.searchFocus === false ? false : true);
        this.searchHighlight = (info.searchHighlight === true ? true : false);
        this.closeOnSelect = (info.closeOnSelect === false ? false : true);
        if (info.showContent) {
            this.showContent = info.showContent;
        }
        this.isEnabled = (info.isEnabled === false ? false : true);
        if (info.searchPlaceholder) {
            this.searchPlaceholder = info.searchPlaceholder;
        }
        if (info.searchText) {
            this.searchText = info.searchText;
        }
        if (info.searchingText) {
            this.searchingText = info.searchingText;
        }
        if (info.placeholderText) {
            this.placeholderText = info.placeholderText;
        }
        this.allowDeselect = (info.allowDeselect === true ? true : false);
        this.allowDeselectOption = (info.allowDeselectOption === true ? true : false);
        this.hideSelectedOption = (info.hideSelectedOption === true ? true : false);
        if (info.deselectLabel) {
            this.deselectLabel = info.deselectLabel;
        }
        if (info.valuesUseText) {
            this.valuesUseText = info.valuesUseText;
        }
        if (info.showOptionTooltips) {
            this.showOptionTooltips = info.showOptionTooltips;
        }
        if (info.selectByGroup) {
            this.selectByGroup = info.selectByGroup;
        }
        if (info.limit) {
            this.limit = info.limit;
        }
        if (info.searchFilter) {
            this.searchFilter = info.searchFilter;
        }
        if (info.timeoutDelay != null) {
            this.timeoutDelay = info.timeoutDelay;
        }
        this.addToBody = (info.addToBody === true ? true : false);
    }
    searchFilter(opt, search) {
        return opt.text.toLowerCase().indexOf(search.toLowerCase()) !== -1;
    }
}

function hasClassInTree(element, className) {
    function hasClass(e, c) {
        if (!(!c || !e || !e.classList || !e.classList.contains(c))) {
            return e;
        }
        return null;
    }
    function parentByClass(e, c) {
        if (!e || e === document) {
            return null;
        }
        else if (hasClass(e, c)) {
            return e;
        }
        else {
            return parentByClass(e.parentNode, c);
        }
    }
    return hasClass(element, className) || parentByClass(element, className);
}
function ensureElementInView(container, element) {
    const cTop = container.scrollTop + container.offsetTop;
    const cBottom = cTop + container.clientHeight;
    const eTop = element.offsetTop;
    const eBottom = eTop + element.clientHeight;
    if (eTop < cTop) {
        container.scrollTop -= (cTop - eTop);
    }
    else if (eBottom > cBottom) {
        container.scrollTop += (eBottom - cBottom);
    }
}
function putContent(el, currentPosition, isOpen) {
    const height = el.offsetHeight;
    const rect = el.getBoundingClientRect();
    const elemTop = (isOpen ? rect.top : rect.top - height);
    const elemBottom = (isOpen ? rect.bottom : rect.bottom + height);
    if (elemTop <= 0) {
        return 'below';
    }
    if (elemBottom >= window.innerHeight) {
        return 'above';
    }
    return (isOpen ? currentPosition : 'below');
}
function debounce(func, wait = 100, immediate = false) {
    let timeout;
    return function (...args) {
        const context = self;
        const later = () => {
            timeout = null;
            if (!immediate) {
                func.apply(context, args);
            }
        };
        const callNow = immediate && !timeout;
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
        if (callNow) {
            func.apply(context, args);
        }
    };
}
function isValueInArrayOfObjects(selected, key, value) {
    if (!Array.isArray(selected)) {
        return selected[key] === value;
    }
    for (const s of selected) {
        if (s && s[key] && s[key] === value) {
            return true;
        }
    }
    return false;
}
function highlight(str, search, className) {
    let completedString = str;
    const regex = new RegExp('(' + search.trim() + ')(?![^<]*>[^<>]*</)', 'i');
    if (!str.match(regex)) {
        return str;
    }
    const matchStartPosition = str.match(regex).index;
    const matchEndPosition = matchStartPosition + str.match(regex)[0].toString().length;
    const originalTextFoundByRegex = str.substring(matchStartPosition, matchEndPosition);
    completedString = completedString.replace(regex, `<mark class="${className}">${originalTextFoundByRegex}</mark>`);
    return completedString;
}
function kebabCase(str) {
    const result = str.replace(/[A-Z\u00C0-\u00D6\u00D8-\u00DE]/g, (match) => '-' + match.toLowerCase());
    return (str[0] === str[0].toUpperCase())
        ? result.substring(1)
        : result;
}
(() => {
    const w = window;
    if (typeof w.CustomEvent === 'function') {
        return;
    }
    function CustomEvent(event, params) {
        params = params || { bubbles: false, cancelable: false, detail: undefined };
        const evt = document.createEvent('CustomEvent');
        evt.initCustomEvent(event, params.bubbles, params.cancelable, params.detail);
        return evt;
    }
    CustomEvent.prototype = w.Event.prototype;
    w.CustomEvent = CustomEvent;
})();

class Select {
    constructor(info) {
        this.triggerMutationObserver = true;
        this.element = info.select;
        this.main = info.main;
        if (this.element.disabled) {
            this.main.config.isEnabled = false;
        }
        this.addAttributes();
        this.addEventListeners();
        this.mutationObserver = null;
        this.addMutationObserver();
        const el = this.element;
        el.slim = info.main;
    }
    setValue() {
        if (!this.main.data.getSelected()) {
            return;
        }
        if (this.main.config.isMultiple) {
            const selected = this.main.data.getSelected();
            const options = this.element.options;
            for (const o of options) {
                o.selected = false;
                for (const s of selected) {
                    if (s.value === o.value) {
                        o.selected = true;
                    }
                }
            }
        }
        else {
            const selected = this.main.data.getSelected();
            this.element.value = (selected ? selected.value : '');
        }
        this.main.data.isOnChangeEnabled = false;
        this.element.dispatchEvent(new CustomEvent('change', { bubbles: true }));
        this.main.data.isOnChangeEnabled = true;
    }
    addAttributes() {
        this.element.tabIndex = -1;
        this.element.style.display = 'block';
        this.element.style.width = '1px';
        this.element.style.height = '1px';
        this.element.style.opacity = '0';
        this.element.dataset.ssid = this.main.config.id;
        this.element.setAttribute('aria-hidden', 'true');
    }
    addEventListeners() {
        this.element.addEventListener('change', (e) => {
            this.main.data.setSelectedFromSelect();
            this.main.render();
        });
    }
    addMutationObserver() {
        if (this.main.config.isAjax) {
            return;
        }
        this.mutationObserver = new MutationObserver((mutations) => {
            if (!this.triggerMutationObserver) {
                return;
            }
            this.main.data.parseSelectData();
            this.main.data.setSelectedFromSelect();
            this.main.render();
            mutations.forEach((mutation) => {
                if (mutation.attributeName === 'class') {
                    this.main.slim.updateContainerDivClass(this.main.slim.container);
                }
            });
        });
        this.observeMutationObserver();
    }
    observeMutationObserver() {
        if (!this.mutationObserver) {
            return;
        }
        this.mutationObserver.observe(this.element, {
            attributes: true,
            childList: true,
            characterData: true
        });
    }
    disconnectMutationObserver() {
        if (this.mutationObserver) {
            this.mutationObserver.disconnect();
        }
    }
    create(data) {
        this.element.innerHTML = '';
        for (const d of data) {
            if (d.hasOwnProperty('options')) {
                const optgroupObject = d;
                const optgroupEl = document.createElement('optgroup');
                optgroupEl.label = optgroupObject.label;
                if (optgroupObject.options) {
                    for (const oo of optgroupObject.options) {
                        optgroupEl.appendChild(this.createOption(oo));
                    }
                }
                this.element.appendChild(optgroupEl);
            }
            else {
                this.element.appendChild(this.createOption(d));
            }
        }
    }
    createOption(info) {
        const optionEl = document.createElement('option');
        optionEl.value = info.value !== '' ? info.value : info.text;
        optionEl.innerHTML = info.innerHTML || info.text;
        if (info.selected) {
            optionEl.selected = info.selected;
        }
        if (info.display === false) {
            optionEl.style.display = 'none';
        }
        if (info.disabled) {
            optionEl.disabled = true;
        }
        if (info.placeholder) {
            optionEl.setAttribute('data-placeholder', 'true');
        }
        if (info.mandatory) {
            optionEl.setAttribute('data-mandatory', 'true');
        }
        if (info.class) {
            info.class.split(' ').forEach((optionClass) => {
                optionEl.classList.add(optionClass);
            });
        }
        if (info.data && typeof info.data === 'object') {
            Object.keys(info.data).forEach((key) => {
                optionEl.setAttribute('data-' + kebabCase(key), info.data[key]);
            });
        }
        return optionEl;
    }
}

class Data {
    constructor(info) {
        this.contentOpen = false;
        this.contentPosition = 'below';
        this.isOnChangeEnabled = true;
        this.main = info.main;
        this.searchValue = '';
        this.data = [];
        this.filtered = null;
        this.parseSelectData();
        this.setSelectedFromSelect();
    }
    newOption(info) {
        return {
            id: (info.id ? info.id : String(Math.floor(Math.random() * 100000000))),
            value: (info.value ? info.value : ''),
            text: (info.text ? info.text : ''),
            innerHTML: (info.innerHTML ? info.innerHTML : ''),
            selected: (info.selected ? info.selected : false),
            display: (info.display !== undefined ? info.display : true),
            disabled: (info.disabled ? info.disabled : false),
            placeholder: (info.placeholder ? info.placeholder : false),
            class: (info.class ? info.class : undefined),
            data: (info.data ? info.data : {}),
            mandatory: (info.mandatory ? info.mandatory : false)
        };
    }
    add(data) {
        this.data.push({
            id: String(Math.floor(Math.random() * 100000000)),
            value: data.value,
            text: data.text,
            innerHTML: '',
            selected: false,
            display: true,
            disabled: false,
            placeholder: false,
            class: undefined,
            mandatory: data.mandatory,
            data: {}
        });
    }
    parseSelectData() {
        this.data = [];
        const nodes = this.main.select.element.childNodes;
        for (const n of nodes) {
            if (n.nodeName === 'OPTGROUP') {
                const node = n;
                const optgroup = {
                    label: node.label,
                    options: []
                };
                const options = n.childNodes;
                for (const o of options) {
                    if (o.nodeName === 'OPTION') {
                        const option = this.pullOptionData(o);
                        optgroup.options.push(option);
                        if (option.placeholder && option.text.trim() !== '') {
                            this.main.config.placeholderText = option.text;
                        }
                    }
                }
                this.data.push(optgroup);
            }
            else if (n.nodeName === 'OPTION') {
                const option = this.pullOptionData(n);
                this.data.push(option);
                if (option.placeholder && option.text.trim() !== '') {
                    this.main.config.placeholderText = option.text;
                }
            }
        }
    }
    pullOptionData(option) {
        return {
            id: (option.dataset ? option.dataset.id : false) || String(Math.floor(Math.random() * 100000000)),
            value: option.value,
            text: option.text,
            innerHTML: option.innerHTML,
            selected: option.selected,
            disabled: option.disabled,
            placeholder: option.dataset.placeholder === 'true',
            class: option.className,
            style: option.style.cssText,
            data: option.dataset,
            mandatory: (option.dataset ? option.dataset.mandatory === 'true' : false)
        };
    }
    setSelectedFromSelect() {
        if (this.main.config.isMultiple) {
            const options = this.main.select.element.options;
            const newSelected = [];
            for (const o of options) {
                if (o.selected) {
                    const newOption = this.getObjectFromData(o.value, 'value');
                    if (newOption && newOption.id) {
                        newSelected.push(newOption.id);
                    }
                }
            }
            this.setSelected(newSelected, 'id');
        }
        else {
            const element = this.main.select.element;
            if (element.selectedIndex !== -1) {
                const option = element.options[element.selectedIndex];
                const value = option.value;
                this.setSelected(value, 'value');
            }
        }
    }
    setSelected(value, type = 'id') {
        for (const d of this.data) {
            if (d.hasOwnProperty('label')) {
                if (d.hasOwnProperty('options')) {
                    const options = d.options;
                    if (options) {
                        for (const o of options) {
                            if (o.placeholder) {
                                continue;
                            }
                            o.selected = this.shouldBeSelected(o, value, type);
                        }
                    }
                }
            }
            else {
                d.selected = this.shouldBeSelected(d, value, type);
            }
        }
    }
    shouldBeSelected(option, value, type = 'id') {
        if (Array.isArray(value)) {
            for (const v of value) {
                if (type in option && String(option[type]) === String(v)) {
                    return true;
                }
            }
        }
        else {
            if (type in option && String(option[type]) === String(value)) {
                return true;
            }
        }
        return false;
    }
    getSelected() {
        let value = { text: '', placeholder: this.main.config.placeholderText };
        const values = [];
        for (const d of this.data) {
            if (d.hasOwnProperty('label')) {
                if (d.hasOwnProperty('options')) {
                    const options = d.options;
                    if (options) {
                        for (const o of options) {
                            if (o.selected) {
                                if (!this.main.config.isMultiple) {
                                    value = o;
                                }
                                else {
                                    values.push(o);
                                }
                            }
                        }
                    }
                }
            }
            else {
                if (d.selected) {
                    if (!this.main.config.isMultiple) {
                        value = d;
                    }
                    else {
                        values.push(d);
                    }
                }
            }
        }
        if (this.main.config.isMultiple) {
            return values;
        }
        return value;
    }
    addToSelected(value, type = 'id') {
        if (this.main.config.isMultiple) {
            const values = [];
            const selected = this.getSelected();
            if (Array.isArray(selected)) {
                for (const s of selected) {
                    values.push(s[type]);
                }
            }
            values.push(value);
            this.setSelected(values, type);
        }
    }
    removeFromSelected(value, type = 'id') {
        if (this.main.config.isMultiple) {
            const values = [];
            const selected = this.getSelected();
            for (const s of selected) {
                if (String(s[type]) !== String(value)) {
                    values.push(s[type]);
                }
            }
            this.setSelected(values, type);
        }
    }
    onDataChange() {
        if (this.main.onChange && this.isOnChangeEnabled) {
            this.main.onChange(JSON.parse(JSON.stringify(this.getSelected())));
        }
    }
    getObjectFromData(value, type = 'id') {
        for (const d of this.data) {
            if (type in d && String(d[type]) === String(value)) {
                return d;
            }
            if (d.hasOwnProperty('options')) {
                const optgroupObject = d;
                if (optgroupObject.options) {
                    for (const oo of optgroupObject.options) {
                        if (String(oo[type]) === String(value)) {
                            return oo;
                        }
                    }
                }
            }
        }
        return null;
    }
    search(search) {
        this.searchValue = search;
        if (search.trim() === '') {
            this.filtered = null;
            return;
        }
        const searchFilter = this.main.config.searchFilter;
        const valuesArray = this.data.slice(0);
        search = search.trim();
        const filtered = valuesArray.map((obj) => {
            if (obj.hasOwnProperty('options')) {
                const optgroupObj = obj;
                let options = [];
                if (optgroupObj.options) {
                    options = optgroupObj.options.filter((opt) => {
                        return searchFilter(opt, search);
                    });
                }
                if (options.length !== 0) {
                    const optgroup = Object.assign({}, optgroupObj);
                    optgroup.options = options;
                    return optgroup;
                }
            }
            if (obj.hasOwnProperty('text')) {
                const optionObj = obj;
                if (searchFilter(optionObj, search)) {
                    return obj;
                }
            }
            return null;
        });
        this.filtered = filtered.filter((info) => info);
    }
}
function validateData(data) {
    if (!data) {
        console.error('Data must be an array of objects');
        return false;
    }
    let isValid = false;
    let errorCount = 0;
    for (const d of data) {
        if (d.hasOwnProperty('label')) {
            if (d.hasOwnProperty('options')) {
                const optgroup = d;
                const options = optgroup.options;
                if (options) {
                    for (const o of options) {
                        isValid = validateOption(o);
                        if (!isValid) {
                            errorCount++;
                        }
                    }
                }
            }
        }
        else {
            const option = d;
            isValid = validateOption(option);
            if (!isValid) {
                errorCount++;
            }
        }
    }
    return errorCount === 0;
}
function validateOption(option) {
    if (option.text === undefined) {
        console.error('Data object option must have at least have a text value. Check object: ' + JSON.stringify(option));
        return false;
    }
    return true;
}

class Slim {
    constructor(info) {
        this.main = info.main;
        this.container = this.containerDiv();
        this.content = this.contentDiv();
        this.search = this.searchDiv();
        this.list = this.listDiv();
        this.options();
        this.singleSelected = null;
        this.multiSelected = null;
        if (this.main.config.isMultiple) {
            this.multiSelected = this.multiSelectedDiv();
            if (this.multiSelected) {
                this.container.appendChild(this.multiSelected.container);
            }
        }
        else {
            this.singleSelected = this.singleSelectedDiv();
            this.container.appendChild(this.singleSelected.container);
        }
        if (this.main.config.addToBody) {
            this.content.classList.add(this.main.config.id);
            document.body.appendChild(this.content);
        }
        else {
            this.container.appendChild(this.content);
        }
        this.content.appendChild(this.search.container);
        this.content.appendChild(this.list);
    }
    containerDiv() {
        const container = document.createElement('div');
        container.style.cssText = this.main.config.style;
        this.updateContainerDivClass(container);
        return container;
    }
    updateContainerDivClass(container) {
        this.main.config.class = this.main.select.element.className.split(' ');
        container.className = '';
        container.classList.add(this.main.config.id);
        container.classList.add(this.main.config.main);
        for (const c of this.main.config.class) {
            if (c.trim() !== '') {
                container.classList.add(c);
            }
        }
    }
    singleSelectedDiv() {
        const container = document.createElement('div');
        container.classList.add(this.main.config.singleSelected);
        const placeholder = document.createElement('span');
        placeholder.classList.add('placeholder');
        container.appendChild(placeholder);
        const deselect = document.createElement('span');
        deselect.innerHTML = this.main.config.deselectLabel;
        deselect.classList.add('ss-deselect');
        deselect.onclick = (e) => {
            e.stopPropagation();
            if (!this.main.config.isEnabled) {
                return;
            }
            this.main.set('');
        };
        container.appendChild(deselect);
        const arrowContainer = document.createElement('span');
        arrowContainer.classList.add(this.main.config.arrow);
        const arrowIcon = document.createElement('span');
        arrowIcon.classList.add('arrow-down');
        arrowContainer.appendChild(arrowIcon);
        container.appendChild(arrowContainer);
        container.onclick = () => {
            if (!this.main.config.isEnabled) {
                return;
            }
            this.main.data.contentOpen ? this.main.close() : this.main.open();
        };
        return {
            container,
            placeholder,
            deselect,
            arrowIcon: {
                container: arrowContainer,
                arrow: arrowIcon
            }
        };
    }
    placeholder() {
        const selected = this.main.data.getSelected();
        if (selected === null || (selected && selected.placeholder)) {
            const placeholder = document.createElement('span');
            placeholder.classList.add(this.main.config.disabled);
            placeholder.innerHTML = this.main.config.placeholderText;
            if (this.singleSelected) {
                this.singleSelected.placeholder.innerHTML = placeholder.outerHTML;
            }
        }
        else {
            let selectedValue = '';
            if (selected) {
                selectedValue = selected.innerHTML && this.main.config.valuesUseText !== true ? selected.innerHTML : selected.text;
            }
            if (this.singleSelected) {
                this.singleSelected.placeholder.innerHTML = (selected ? selectedValue : '');
            }
        }
    }
    deselect() {
        if (this.singleSelected) {
            if (!this.main.config.allowDeselect) {
                this.singleSelected.deselect.classList.add('ss-hide');
                return;
            }
            if (this.main.selected() === '') {
                this.singleSelected.deselect.classList.add('ss-hide');
            }
            else {
                this.singleSelected.deselect.classList.remove('ss-hide');
            }
        }
    }
    multiSelectedDiv() {
        const container = document.createElement('div');
        container.classList.add(this.main.config.multiSelected);
        const values = document.createElement('div');
        values.classList.add(this.main.config.values);
        container.appendChild(values);
        const add = document.createElement('div');
        add.classList.add(this.main.config.add);
        const plus = document.createElement('span');
        plus.classList.add(this.main.config.plus);
        plus.onclick = (e) => {
            if (this.main.data.contentOpen) {
                this.main.close();
                e.stopPropagation();
            }
        };
        add.appendChild(plus);
        container.appendChild(add);
        container.onclick = (e) => {
            if (!this.main.config.isEnabled) {
                return;
            }
            const target = e.target;
            if (!target.classList.contains(this.main.config.valueDelete)) {
                this.main.data.contentOpen ? this.main.close() : this.main.open();
            }
        };
        return {
            container,
            values,
            add,
            plus
        };
    }
    values() {
        if (!this.multiSelected) {
            return;
        }
        let currentNodes = this.multiSelected.values.childNodes;
        const selected = this.main.data.getSelected();
        let exists;
        const nodesToRemove = [];
        for (const c of currentNodes) {
            exists = true;
            for (const s of selected) {
                if (String(s.id) === String(c.dataset.id)) {
                    exists = false;
                }
            }
            if (exists) {
                nodesToRemove.push(c);
            }
        }
        for (const n of nodesToRemove) {
            n.classList.add('ss-out');
            this.multiSelected.values.removeChild(n);
        }
        currentNodes = this.multiSelected.values.childNodes;
        for (let s = 0; s < selected.length; s++) {
            exists = false;
            for (const c of currentNodes) {
                if (String(selected[s].id) === String(c.dataset.id)) {
                    exists = true;
                }
            }
            if (!exists) {
                if (currentNodes.length === 0 || !HTMLElement.prototype.insertAdjacentElement) {
                    this.multiSelected.values.appendChild(this.valueDiv(selected[s]));
                }
                else if (s === 0) {
                    this.multiSelected.values.insertBefore(this.valueDiv(selected[s]), currentNodes[s]);
                }
                else {
                    currentNodes[s - 1].insertAdjacentElement('afterend', this.valueDiv(selected[s]));
                }
            }
        }
        if (selected.length === 0) {
            const placeholder = document.createElement('span');
            placeholder.classList.add(this.main.config.disabled);
            placeholder.innerHTML = this.main.config.placeholderText;
            this.multiSelected.values.innerHTML = placeholder.outerHTML;
        }
    }
    valueDiv(optionObj) {
        const value = document.createElement('div');
        value.classList.add(this.main.config.value);
        value.dataset.id = optionObj.id;
        const text = document.createElement('span');
        text.classList.add(this.main.config.valueText);
        text.innerHTML = (optionObj.innerHTML && this.main.config.valuesUseText !== true ? optionObj.innerHTML : optionObj.text);
        value.appendChild(text);
        if (!optionObj.mandatory) {
            const deleteSpan = document.createElement('span');
            deleteSpan.classList.add(this.main.config.valueDelete);
            deleteSpan.innerHTML = this.main.config.deselectLabel;
            deleteSpan.onclick = (e) => {
                e.preventDefault();
                e.stopPropagation();
                let shouldUpdate = false;
                if (!this.main.beforeOnChange) {
                    shouldUpdate = true;
                }
                if (this.main.beforeOnChange) {
                    const selected = this.main.data.getSelected();
                    const currentValues = JSON.parse(JSON.stringify(selected));
                    for (let i = 0; i < currentValues.length; i++) {
                        if (currentValues[i].id === optionObj.id) {
                            currentValues.splice(i, 1);
                        }
                    }
                    const beforeOnchange = this.main.beforeOnChange(currentValues);
                    if (beforeOnchange !== false) {
                        shouldUpdate = true;
                    }
                }
                if (shouldUpdate) {
                    this.main.data.removeFromSelected(optionObj.id, 'id');
                    this.main.render();
                    this.main.select.setValue();
                    this.main.data.onDataChange();
                }
            };
            value.appendChild(deleteSpan);
        }
        return value;
    }
    contentDiv() {
        const container = document.createElement('div');
        container.classList.add(this.main.config.content);
        return container;
    }
    searchDiv() {
        const container = document.createElement('div');
        const input = document.createElement('input');
        const addable = document.createElement('div');
        container.classList.add(this.main.config.search);
        const searchReturn = {
            container,
            input
        };
        if (!this.main.config.showSearch) {
            container.classList.add(this.main.config.hide);
            input.readOnly = true;
        }
        input.type = 'search';
        input.placeholder = this.main.config.searchPlaceholder;
        input.tabIndex = 0;
        input.setAttribute('aria-label', this.main.config.searchPlaceholder);
        input.setAttribute('autocapitalize', 'off');
        input.setAttribute('autocomplete', 'off');
        input.setAttribute('autocorrect', 'off');
        input.onclick = (e) => {
            setTimeout(() => {
                const target = e.target;
                if (target.value === '') {
                    this.main.search('');
                }
            }, 10);
        };
        input.onkeydown = (e) => {
            if (e.key === 'ArrowUp') {
                this.main.open();
                this.highlightUp();
                e.preventDefault();
            }
            else if (e.key === 'ArrowDown') {
                this.main.open();
                this.highlightDown();
                e.preventDefault();
            }
            else if (e.key === 'Tab') {
                if (!this.main.data.contentOpen) {
                    setTimeout(() => { this.main.close(); }, this.main.config.timeoutDelay);
                }
                else {
                    this.main.close();
                }
            }
            else if (e.key === 'Enter') {
                e.preventDefault();
            }
        };
        input.onkeyup = (e) => {
            const target = e.target;
            if (e.key === 'Enter') {
                if (this.main.addable && e.ctrlKey) {
                    addable.click();
                    e.preventDefault();
                    e.stopPropagation();
                    return;
                }
                const highlighted = this.list.querySelector('.' + this.main.config.highlighted);
                if (highlighted) {
                    highlighted.click();
                }
            }
            else if (e.key === 'ArrowUp' || e.key === 'ArrowDown');
            else if (e.key === 'Escape') {
                this.main.close();
            }
            else {
                if (this.main.config.showSearch && this.main.data.contentOpen) {
                    this.main.search(target.value);
                }
                else {
                    input.value = '';
                }
            }
            e.preventDefault();
            e.stopPropagation();
        };
        input.onfocus = () => { this.main.open(); };
        container.appendChild(input);
        if (this.main.addable) {
            addable.classList.add(this.main.config.addable);
            addable.innerHTML = '+';
            addable.onclick = (e) => {
                if (this.main.addable) {
                    e.preventDefault();
                    e.stopPropagation();
                    const inputValue = this.search.input.value;
                    if (inputValue.trim() === '') {
                        this.search.input.focus();
                        return;
                    }
                    const addableValue = this.main.addable(inputValue);
                    let addableValueStr = '';
                    if (!addableValue) {
                        return;
                    }
                    if (typeof addableValue === 'object') {
                        const validValue = validateOption(addableValue);
                        if (validValue) {
                            this.main.addData(addableValue);
                            addableValueStr = (addableValue.value ? addableValue.value : addableValue.text);
                        }
                    }
                    else {
                        this.main.addData(this.main.data.newOption({
                            text: addableValue,
                            value: addableValue
                        }));
                        addableValueStr = addableValue;
                    }
                    this.main.search('');
                    setTimeout(() => {
                        this.main.set(addableValueStr, 'value', false, false);
                    }, 100);
                    if (this.main.config.closeOnSelect) {
                        setTimeout(() => {
                            this.main.close();
                        }, 100);
                    }
                }
            };
            container.appendChild(addable);
            searchReturn.addable = addable;
        }
        return searchReturn;
    }
    highlightUp() {
        const highlighted = this.list.querySelector('.' + this.main.config.highlighted);
        let prev = null;
        if (highlighted) {
            prev = highlighted.previousSibling;
            while (prev !== null) {
                if (prev.classList.contains(this.main.config.disabled)) {
                    prev = prev.previousSibling;
                    continue;
                }
                else {
                    break;
                }
            }
        }
        else {
            const allOptions = this.list.querySelectorAll('.' + this.main.config.option + ':not(.' + this.main.config.disabled + ')');
            prev = allOptions[allOptions.length - 1];
        }
        if (prev && prev.classList.contains(this.main.config.optgroupLabel)) {
            prev = null;
        }
        if (prev === null) {
            const parent = highlighted.parentNode;
            if (parent.classList.contains(this.main.config.optgroup)) {
                if (parent.previousSibling) {
                    const prevNodes = parent.previousSibling.querySelectorAll('.' + this.main.config.option + ':not(.' + this.main.config.disabled + ')');
                    if (prevNodes.length) {
                        prev = prevNodes[prevNodes.length - 1];
                    }
                }
            }
        }
        if (prev) {
            if (highlighted) {
                highlighted.classList.remove(this.main.config.highlighted);
            }
            prev.classList.add(this.main.config.highlighted);
            ensureElementInView(this.list, prev);
        }
    }
    highlightDown() {
        const highlighted = this.list.querySelector('.' + this.main.config.highlighted);
        let next = null;
        if (highlighted) {
            next = highlighted.nextSibling;
            while (next !== null) {
                if (next.classList.contains(this.main.config.disabled)) {
                    next = next.nextSibling;
                    continue;
                }
                else {
                    break;
                }
            }
        }
        else {
            next = this.list.querySelector('.' + this.main.config.option + ':not(.' + this.main.config.disabled + ')');
        }
        if (next === null && highlighted !== null) {
            const parent = highlighted.parentNode;
            if (parent.classList.contains(this.main.config.optgroup)) {
                if (parent.nextSibling) {
                    const sibling = parent.nextSibling;
                    next = sibling.querySelector('.' + this.main.config.option + ':not(.' + this.main.config.disabled + ')');
                }
            }
        }
        if (next) {
            if (highlighted) {
                highlighted.classList.remove(this.main.config.highlighted);
            }
            next.classList.add(this.main.config.highlighted);
            ensureElementInView(this.list, next);
        }
    }
    listDiv() {
        const list = document.createElement('div');
        list.id = this.main.config.id;
        list.classList.add(this.main.config.list);
        list.setAttribute('role', 'listbox');
        list.setAttribute('aria-label', 'Pilihan ' + this.main.config.id);
        return list;
    }
    options(content = '') {
        const data = this.main.data.filtered || this.main.data.data;
        this.list.innerHTML = '';
        if (content !== '') {
            const searching = document.createElement('div');
            searching.classList.add(this.main.config.option);
            searching.classList.add(this.main.config.disabled);
            searching.innerHTML = content;
            this.list.appendChild(searching);
            return;
        }
        if (this.main.config.isAjax && this.main.config.isSearching) {
            const searching = document.createElement('div');
            searching.classList.add(this.main.config.option);
            searching.classList.add(this.main.config.disabled);
            searching.innerHTML = this.main.config.searchingText;
            this.list.appendChild(searching);
            return;
        }
        if (data.length === 0) {
            const noResults = document.createElement('div');
            noResults.classList.add(this.main.config.option);
            noResults.classList.add(this.main.config.disabled);
            noResults.innerHTML = this.main.config.searchText;
            this.list.appendChild(noResults);
            return;
        }
        for (const d of data) {
            if (d.hasOwnProperty('label')) {
                const item = d;
                const optgroupEl = document.createElement('div');
                optgroupEl.classList.add(this.main.config.optgroup);
                const optgroupLabel = document.createElement('div');
                optgroupLabel.classList.add(this.main.config.optgroupLabel);
                if (this.main.config.selectByGroup && this.main.config.isMultiple) {
                    optgroupLabel.classList.add(this.main.config.optgroupLabelSelectable);
                }
                optgroupLabel.innerHTML = item.label;
                optgroupEl.appendChild(optgroupLabel);
                const options = item.options;
                if (options) {
                    for (const o of options) {
                        optgroupEl.appendChild(this.option(o));
                    }
                    if (this.main.config.selectByGroup && this.main.config.isMultiple) {
                        const master = this;
                        optgroupLabel.addEventListener('click', (e) => {
                            e.preventDefault();
                            e.stopPropagation();
                            for (const childEl of optgroupEl.children) {
                                if (childEl.className.indexOf(master.main.config.option) !== -1) {
                                    childEl.click();
                                }
                            }
                        });
                    }
                }
                this.list.appendChild(optgroupEl);
            }
            else {
                this.list.appendChild(this.option(d));
            }
        }
    }
    option(data) {
        if (data.placeholder) {
            const placeholder = document.createElement('div');
            placeholder.classList.add(this.main.config.option);
            placeholder.classList.add(this.main.config.hide);
            return placeholder;
        }
        const optionEl = document.createElement('div');
        optionEl.classList.add(this.main.config.option);
        optionEl.setAttribute('role', 'option');
        if (data.class) {
            data.class.split(' ').forEach((dataClass) => {
                optionEl.classList.add(dataClass);
            });
        }
        if (data.style) {
            optionEl.style.cssText = data.style;
        }
        const selected = this.main.data.getSelected();
        optionEl.dataset.id = data.id;
        if (this.main.config.searchHighlight && this.main.slim && data.innerHTML && this.main.slim.search.input.value.trim() !== '') {
            optionEl.innerHTML = highlight(data.innerHTML, this.main.slim.search.input.value, this.main.config.searchHighlighter);
        }
        else if (data.innerHTML) {
            optionEl.innerHTML = data.innerHTML;
        }
        if (this.main.config.showOptionTooltips && optionEl.textContent) {
            optionEl.setAttribute('title', optionEl.textContent);
        }
        const master = this;
        optionEl.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            const element = this;
            const elementID = element.dataset.id;
            if (data.selected === true && master.main.config.allowDeselectOption) {
                let shouldUpdate = false;
                if (!master.main.beforeOnChange || !master.main.config.isMultiple) {
                    shouldUpdate = true;
                }
                if (master.main.beforeOnChange && master.main.config.isMultiple) {
                    const selectedValues = master.main.data.getSelected();
                    const currentValues = JSON.parse(JSON.stringify(selectedValues));
                    for (let i = 0; i < currentValues.length; i++) {
                        if (currentValues[i].id === elementID) {
                            currentValues.splice(i, 1);
                        }
                    }
                    const beforeOnchange = master.main.beforeOnChange(currentValues);
                    if (beforeOnchange !== false) {
                        shouldUpdate = true;
                    }
                }
                if (shouldUpdate) {
                    if (master.main.config.isMultiple) {
                        master.main.data.removeFromSelected(elementID, 'id');
                        master.main.render();
                        master.main.select.setValue();
                        master.main.data.onDataChange();
                    }
                    else {
                        master.main.set('');
                    }
                }
            }
            else {
                if (data.disabled || data.selected) {
                    return;
                }
                if (master.main.config.limit && Array.isArray(selected) && master.main.config.limit <= selected.length) {
                    return;
                }
                if (master.main.beforeOnChange) {
                    let value;
                    const objectInfo = JSON.parse(JSON.stringify(master.main.data.getObjectFromData(elementID)));
                    objectInfo.selected = true;
                    if (master.main.config.isMultiple) {
                        value = JSON.parse(JSON.stringify(selected));
                        value.push(objectInfo);
                    }
                    else {
                        value = JSON.parse(JSON.stringify(objectInfo));
                    }
                    const beforeOnchange = master.main.beforeOnChange(value);
                    if (beforeOnchange !== false) {
                        master.main.set(elementID, 'id', master.main.config.closeOnSelect);
                    }
                }
                else {
                    master.main.set(elementID, 'id', master.main.config.closeOnSelect);
                }
            }
        });
        const isSelected = selected && isValueInArrayOfObjects(selected, 'id', data.id);
        if (data.disabled || isSelected) {
            optionEl.onclick = null;
            if (!master.main.config.allowDeselectOption) {
                optionEl.classList.add(this.main.config.disabled);
            }
            if (master.main.config.hideSelectedOption) {
                optionEl.classList.add(this.main.config.hide);
            }
        }
        if (isSelected) {
            optionEl.classList.add(this.main.config.optionSelected);
        }
        else {
            optionEl.classList.remove(this.main.config.optionSelected);
        }
        return optionEl;
    }
}

class SlimSelect {
    constructor(info) {
        this.ajax = null;
        this.addable = null;
        this.beforeOnChange = null;
        this.onChange = null;
        this.beforeOpen = null;
        this.afterOpen = null;
        this.beforeClose = null;
        this.afterClose = null;
        this.windowScroll = debounce((e) => {
            if (this.data.contentOpen) {
                if (putContent(this.slim.content, this.data.contentPosition, this.data.contentOpen) === 'above') {
                    this.moveContentAbove();
                }
                else {
                    this.moveContentBelow();
                }
            }
        });
        this.documentClick = (e) => {
            if (e.target && !hasClassInTree(e.target, this.config.id)) {
                this.close();
            }
        };
        const selectElement = this.validate(info);
        if (selectElement.dataset.ssid) {
            this.destroy(selectElement.dataset.ssid);
        }
        if (info.ajax) {
            this.ajax = info.ajax;
        }
        if (info.addable) {
            this.addable = info.addable;
        }
        this.config = new Config({
            select: selectElement,
            isAjax: (info.ajax ? true : false),
            showSearch: info.showSearch,
            searchPlaceholder: info.searchPlaceholder,
            searchText: info.searchText,
            searchingText: info.searchingText,
            searchFocus: info.searchFocus,
            searchHighlight: info.searchHighlight,
            searchFilter: info.searchFilter,
            closeOnSelect: info.closeOnSelect,
            showContent: info.showContent,
            placeholderText: info.placeholder,
            allowDeselect: info.allowDeselect,
            allowDeselectOption: info.allowDeselectOption,
            hideSelectedOption: info.hideSelectedOption,
            deselectLabel: info.deselectLabel,
            isEnabled: info.isEnabled,
            valuesUseText: info.valuesUseText,
            showOptionTooltips: info.showOptionTooltips,
            selectByGroup: info.selectByGroup,
            limit: info.limit,
            timeoutDelay: info.timeoutDelay,
            addToBody: info.addToBody
        });
        this.select = new Select({
            select: selectElement,
            main: this
        });
        this.data = new Data({ main: this });
        this.slim = new Slim({ main: this });
        if (this.select.element.parentNode) {
            this.select.element.parentNode.insertBefore(this.slim.container, this.select.element.nextSibling);
        }
        if (info.data) {
            this.setData(info.data);
        }
        else {
            this.render();
        }
        document.addEventListener('click', this.documentClick);
        if (this.config.showContent === 'auto') {
            window.addEventListener('scroll', this.windowScroll, false);
        }
        if (info.beforeOnChange) {
            this.beforeOnChange = info.beforeOnChange;
        }
        if (info.onChange) {
            this.onChange = info.onChange;
        }
        if (info.beforeOpen) {
            this.beforeOpen = info.beforeOpen;
        }
        if (info.afterOpen) {
            this.afterOpen = info.afterOpen;
        }
        if (info.beforeClose) {
            this.beforeClose = info.beforeClose;
        }
        if (info.afterClose) {
            this.afterClose = info.afterClose;
        }
        if (!this.config.isEnabled) {
            this.disable();
        }
    }
    validate(info) {
        const select = (typeof info.select === 'string' ? document.querySelector(info.select) : info.select);
        if (!select) {
            throw new Error('Could not find select element');
        }
        if (select.tagName !== 'SELECT') {
            throw new Error('Element isnt of type select');
        }
        return select;
    }
    selected() {
        if (this.config.isMultiple) {
            const selected = this.data.getSelected();
            const outputSelected = [];
            for (const s of selected) {
                outputSelected.push(s.value);
            }
            return outputSelected;
        }
        else {
            const selected = this.data.getSelected();
            return (selected ? selected.value : '');
        }
    }
    set(value, type = 'value', close = true, render = true) {
        if (this.config.isMultiple && !Array.isArray(value)) {
            this.data.addToSelected(value, type);
        }
        else {
            this.data.setSelected(value, type);
        }
        this.select.setValue();
        this.data.onDataChange();
        this.render();
        if (this.config.hideSelectedOption && this.config.isMultiple && this.data.getSelected().length === this.data.data.length) {
            close = true;
        }
        if (close) {
            this.close();
        }
    }
    setSelected(value, type = 'value', close = true, render = true) {
        this.set(value, type, close, render);
    }
    setData(data) {
        const isValid = validateData(data);
        if (!isValid) {
            console.error('Validation problem on: #' + this.select.element.id);
            return;
        }
        const newData = JSON.parse(JSON.stringify(data));
        const selected = this.data.getSelected();
        for (let i = 0; i < newData.length; i++) {
            if (!newData[i].value && !newData[i].placeholder) {
                newData[i].value = newData[i].text;
            }
        }
        if (this.config.isAjax && selected) {
            if (this.config.isMultiple) {
                const reverseSelected = selected.reverse();
                for (const r of reverseSelected) {
                    newData.unshift(r);
                }
            }
            else {
                newData.unshift(selected);
                for (let i = 0; i < newData.length; i++) {
                    if (!newData[i].placeholder && newData[i].value === selected.value && newData[i].text === selected.text) {
                        newData.splice(i, 1);
                    }
                }
                let hasPlaceholder = false;
                for (let i = 0; i < newData.length; i++) {
                    if (newData[i].placeholder) {
                        hasPlaceholder = true;
                    }
                }
                if (!hasPlaceholder) {
                    newData.unshift({ text: '', placeholder: true });
                }
            }
        }
        this.select.create(newData);
        this.data.parseSelectData();
        this.data.setSelectedFromSelect();
    }
    addData(data) {
        const isValid = validateData([data]);
        if (!isValid) {
            console.error('Validation problem on: #' + this.select.element.id);
            return;
        }
        this.data.add(this.data.newOption(data));
        this.select.create(this.data.data);
        this.data.parseSelectData();
        this.data.setSelectedFromSelect();
        this.render();
    }
    open() {
        if (!this.config.isEnabled) {
            return;
        }
        if (this.data.contentOpen) {
            return;
        }
        if (this.config.hideSelectedOption && this.config.isMultiple && this.data.getSelected().length === this.data.data.length) {
            return;
        }
        if (this.beforeOpen) {
            this.beforeOpen();
        }
        if (this.config.isMultiple && this.slim.multiSelected) {
            this.slim.multiSelected.plus.classList.add('ss-cross');
        }
        else if (this.slim.singleSelected) {
            this.slim.singleSelected.arrowIcon.arrow.classList.remove('arrow-down');
            this.slim.singleSelected.arrowIcon.arrow.classList.add('arrow-up');
        }
        this.slim[(this.config.isMultiple ? 'multiSelected' : 'singleSelected')].container.classList.add((this.data.contentPosition === 'above' ? this.config.openAbove : this.config.openBelow));
        if (this.config.addToBody) {
            const containerRect = this.slim.container.getBoundingClientRect();
            this.slim.content.style.top = (containerRect.top + containerRect.height + window.scrollY) + 'px';
            this.slim.content.style.left = (containerRect.left + window.scrollX) + 'px';
            this.slim.content.style.width = containerRect.width + 'px';
        }
        this.slim.content.classList.add(this.config.open);
        if (this.config.showContent.toLowerCase() === 'up') {
            this.moveContentAbove();
        }
        else if (this.config.showContent.toLowerCase() === 'down') {
            this.moveContentBelow();
        }
        else {
            if (putContent(this.slim.content, this.data.contentPosition, this.data.contentOpen) === 'above') {
                this.moveContentAbove();
            }
            else {
                this.moveContentBelow();
            }
        }
        if (!this.config.isMultiple) {
            const selected = this.data.getSelected();
            if (selected) {
                const selectedId = selected.id;
                const selectedOption = this.slim.list.querySelector('[data-id="' + selectedId + '"]');
                if (selectedOption) {
                    ensureElementInView(this.slim.list, selectedOption);
                }
            }
        }
        setTimeout(() => {
            this.data.contentOpen = true;
            if (this.config.searchFocus) {
                this.slim.search.input.focus();
            }
            if (this.afterOpen) {
                this.afterOpen();
            }
        }, this.config.timeoutDelay);
    }
    close() {
        if (!this.data.contentOpen) {
            return;
        }
        if (this.beforeClose) {
            this.beforeClose();
        }
        if (this.config.isMultiple && this.slim.multiSelected) {
            this.slim.multiSelected.container.classList.remove(this.config.openAbove);
            this.slim.multiSelected.container.classList.remove(this.config.openBelow);
            this.slim.multiSelected.plus.classList.remove('ss-cross');
        }
        else if (this.slim.singleSelected) {
            this.slim.singleSelected.container.classList.remove(this.config.openAbove);
            this.slim.singleSelected.container.classList.remove(this.config.openBelow);
            this.slim.singleSelected.arrowIcon.arrow.classList.add('arrow-down');
            this.slim.singleSelected.arrowIcon.arrow.classList.remove('arrow-up');
        }
        this.slim.content.classList.remove(this.config.open);
        this.data.contentOpen = false;
        this.search('');
        setTimeout(() => {
            this.slim.content.removeAttribute('style');
            this.data.contentPosition = 'below';
            if (this.config.isMultiple && this.slim.multiSelected) {
                this.slim.multiSelected.container.classList.remove(this.config.openAbove);
                this.slim.multiSelected.container.classList.remove(this.config.openBelow);
            }
            else if (this.slim.singleSelected) {
                this.slim.singleSelected.container.classList.remove(this.config.openAbove);
                this.slim.singleSelected.container.classList.remove(this.config.openBelow);
            }
            this.slim.search.input.blur();
            if (this.afterClose) {
                this.afterClose();
            }
        }, this.config.timeoutDelay);
    }
    moveContentAbove() {
        let selectHeight = 0;
        if (this.config.isMultiple && this.slim.multiSelected) {
            selectHeight = this.slim.multiSelected.container.offsetHeight;
        }
        else if (this.slim.singleSelected) {
            selectHeight = this.slim.singleSelected.container.offsetHeight;
        }
        const contentHeight = this.slim.content.offsetHeight;
        const height = selectHeight + contentHeight - 1;
        this.slim.content.style.margin = '-' + height + 'px 0 0 0';
        this.slim.content.style.height = (height - selectHeight + 1) + 'px';
        this.slim.content.style.transformOrigin = 'center bottom';
        this.data.contentPosition = 'above';
        if (this.config.isMultiple && this.slim.multiSelected) {
            this.slim.multiSelected.container.classList.remove(this.config.openBelow);
            this.slim.multiSelected.container.classList.add(this.config.openAbove);
        }
        else if (this.slim.singleSelected) {
            this.slim.singleSelected.container.classList.remove(this.config.openBelow);
            this.slim.singleSelected.container.classList.add(this.config.openAbove);
        }
    }
    moveContentBelow() {
        this.data.contentPosition = 'below';
        if (this.config.isMultiple && this.slim.multiSelected) {
            this.slim.multiSelected.container.classList.remove(this.config.openAbove);
            this.slim.multiSelected.container.classList.add(this.config.openBelow);
        }
        else if (this.slim.singleSelected) {
            this.slim.singleSelected.container.classList.remove(this.config.openAbove);
            this.slim.singleSelected.container.classList.add(this.config.openBelow);
        }
    }
    enable() {
        this.config.isEnabled = true;
        if (this.config.isMultiple && this.slim.multiSelected) {
            this.slim.multiSelected.container.classList.remove(this.config.disabled);
        }
        else if (this.slim.singleSelected) {
            this.slim.singleSelected.container.classList.remove(this.config.disabled);
        }
        this.select.triggerMutationObserver = false;
        this.select.element.disabled = false;
        this.slim.search.input.disabled = false;
        this.select.triggerMutationObserver = true;
    }
    disable() {
        this.config.isEnabled = false;
        if (this.config.isMultiple && this.slim.multiSelected) {
            this.slim.multiSelected.container.classList.add(this.config.disabled);
        }
        else if (this.slim.singleSelected) {
            this.slim.singleSelected.container.classList.add(this.config.disabled);
        }
        this.select.triggerMutationObserver = false;
        this.select.element.disabled = true;
        this.slim.search.input.disabled = true;
        this.select.triggerMutationObserver = true;
    }
    search(value) {
        if (this.data.searchValue === value) {
            return;
        }
        this.slim.search.input.value = value;
        if (this.config.isAjax) {
            const master = this;
            this.config.isSearching = true;
            this.render();
            if (this.ajax) {
                this.ajax(value, (info) => {
                    master.config.isSearching = false;
                    if (Array.isArray(info)) {
                        info.unshift({ text: '', placeholder: true });
                        master.setData(info);
                        master.data.search(value);
                        master.render();
                    }
                    else if (typeof info === 'string') {
                        master.slim.options(info);
                    }
                    else {
                        master.render();
                    }
                });
            }
        }
        else {
            this.data.search(value);
            this.render();
        }
    }
    setSearchText(text) {
        this.config.searchText = text;
    }
    render() {
        if (this.config.isMultiple) {
            this.slim.values();
        }
        else {
            this.slim.placeholder();
            this.slim.deselect();
        }
        this.slim.options();
    }
    destroy(id = null) {
        const slim = (id ? document.querySelector('.' + id + '.ss-main') : this.slim.container);
        const select = (id ? document.querySelector(`[data-ssid=${id}]`) : this.select.element);
        if (!slim || !select) {
            return;
        }
        document.removeEventListener('click', this.documentClick);
        if (this.config.showContent === 'auto') {
            window.removeEventListener('scroll', this.windowScroll, false);
        }
        select.style.display = 'revert';
        select.style.width = 'revert';
        select.style.height = 'revert';
        select.style.opacity = 'revert';
        delete select.dataset.ssid;
        const el = select;
        el.slim = null;
        if (slim.parentElement) {
            slim.parentElement.removeChild(slim);
        }
        if (this.config.addToBody) {
            const slimContent = (id ? document.querySelector('.' + id + '.ss-content') : this.slim.content);
            if (!slimContent) {
                return;
            }
            document.body.removeChild(slimContent);
        }
    }
}

export { SlimSelect as default };
