@if ($rekRangka->pjax())
<script>
    @if ($userRangka)
        if (!document.getElementById("tbl-menu")?.innerHTML.trim()) {
            lemparXHR({
            tujuan : "#tbl-menu",
            tautan : "{!! $urlRangka->route('komponen', ['komponen' => 'menu', 'fragment' => 'avatar'], false) !!}",
            normalview : true
            });

            lemparXHR({
            tujuan : "#menu-avatar",
            tautan : "{!! $urlRangka->route('komponen', ['komponen' => 'menu', 'fragment' => 'menu-avatar'], false) !!}",
            normalview : true
            });
        };

        if (!document.getElementById("pilih-sumber_daya")?.innerHTML.trim()) {
            lemparXHR({
            tujuan : "#pilih-sumber_daya",
            tautan : "{!! $urlRangka->route('komponen', ['komponen' => 'menu', 'fragment' => 'pilih-sumber_daya'], false) !!}",
            normalview : true
            });

            lemparXHR({
            tujuan : "#menu-aplikasi",
            tautan : "{!! $urlRangka->route('komponen', ['komponen' => 'menu', 'fragment' => 'menu-aplikasi'], false) !!}",
            normalview : true
            });
        };

        @else
        document.getElementById("pilih-sumber_daya")?.replaceChildren();
        document.getElementById("menu-aplikasi")?.replaceChildren();
        document.getElementById("tbl-menu")?.replaceChildren();
        document.getElementById("menu-avatar")?.replaceChildren();
        
        @endif

        @if($userRangka && $rekRangka->routeIs('sdm.*', 'register'))
        if (!document.getElementById("navigasi-sdm")?.innerHTML.trim()) {
            lemparXHR({
            tujuan : "#navigasi-sdm",
            tautan : "{!! $urlRangka->route('komponen', ['komponen' => 'sdm.navigasi'], false) !!}",
            normalview : true
            });
        };
        @else
        var NavSDM = ["/atur", "/tentang-aplikasi"];
        if (!NavSDM.includes(location.pathname)) document.getElementById("navigasi-sdm")?.replaceChildren();
        @endif

</script>
@endif