<!DOCTYPE html>
<html>
<head>
    <title>Jobtest Karyamas Plantation</title>

    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://cdn.tailwindcss.com"></script>


    <style>
        #map {
            height: 100vh;
        }


        .leaflet-control-zoom {
            margin-top: 80px;
        }

        #sidebar.closed {
            transform: translateX(-110%);
        }


    </style>
</head>
<body class="font-sans">


<button id="toggleBtn" onclick="toggleSidebar()" 
class="absolute top-4 left-4 z-[1000] bg-white shadow-lg rounded-lg px-3 py-2 transition-all duration-300">
    ☰
</button>

<div id="sidebar" class="
    absolute top-4 left-4 z-[999]
    w-72 max-h-[90%] overflow-y-auto
    bg-white rounded-2xl shadow-xl
    p-4 space-y-4
    transition-all duration-300 ">

    <h2 class="text-lg font-semibold text-gray-800">Filter Wilayah</h2>

    <select id="kabupatenSelect" class="w-full border rounded-lg p-2 text-sm"></select>
    <select id="kecamatanSelect" class="w-full border rounded-lg p-2 text-sm"></select>
    <select id="desaSelect" class="w-full border rounded-lg p-2 text-sm"></select>

    <hr>

    <h2 class="text-lg font-semibold text-gray-800">Tutupan Lahan</h2>

    <label class="flex items-center gap-2 text-sm">
        <input type="checkbox" id="selectAll" checked class="accent-blue-500">
        Select All
    </label>

    <div id="layerList" class="space-y-1 text-sm"></div>

    <hr>

    <h2 class="text-lg font-semibold text-gray-800">Statistik</h2>

    <canvas id="pieChart" class="mt-2"></canvas>

</div>






<div id="map"></div>

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>


<script>
   const map = L.map('map', {
        zoomControl: false
    }).setView([-0.5, 110], 7);

    L.control.zoom({
        position: 'topright'
    }).addTo(map);

    // Basemap
    var osm = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap'
    });

    var satellite = L.tileLayer('https://{s}.google.com/vt/lyrs=s&x={x}&y={y}&z={z}', {
        subdomains:['mt0','mt1','mt2','mt3'],
        attribution: '© Google Satellite'
    });

    var terrain = L.tileLayer('https://{s}.google.com/vt/lyrs=p&x={x}&y={y}&z={z}', {
        subdomains:['mt0','mt1','mt2','mt3'],
        attribution: '© Google Terrain'
    });

    osm.addTo(map);

    var baseMaps = {
        "OpenStreetMap": osm,
        "Satellite": satellite,
        "Terrain": terrain
    };

    L.control.layers(baseMaps).addTo(map);

    
    // simbology titik tuplah

    let colorMap = {};

    function getColor(legenda) {
        if (!colorMap[legenda]) {
            colorMap[legenda] = stringToColor(legenda);
        }
        return colorMap[legenda];
    }

    function stringToColor(str) {
        let hash = 0;

        for (let i = 0; i < str.length; i++) {
            hash = str.charCodeAt(i) + ((hash << 5) - hash);
        }

        let color = "#";

        for (let i = 0; i < 3; i++) {
            let value = (hash >> (i * 8)) & 0xFF;
            color += ("00" + value.toString(16)).slice(-2);
        }

        return color;
    }


    let allGridData = [];

    // layer

    let categoriesSet = new Set();

    let allDesaLayer;
    let allGridLayer = L.layerGroup();
    
    let desaLayer = null;
    let gridLayer = null;

    let legend;
    let legendVisible = true;

    let isUpdating = false;


    // load data desa

    fetch('/desa')
    .then(res => res.json())
    .then(data => {

        allDesaLayer = L.geoJSON(data, {
            onEachFeature: function (feature, layer) {
                let p = feature.properties;

                layer.bindPopup(
                    "<b>Desa:</b> " + p.desa + "<br>" +
                    "<b>Kecamatan:</b> " + p.kecamatan + "<br>" +
                    "<b>Kabupaten:</b> " + p.kabkot
                );
            }
        }).addTo(map);

    });

    // load data titik

    fetch('/tuplah')
    .then(res => res.json())
    .then(data => {

        allGridData = data; 

        allGridLayer = L.layerGroup();

        data.forEach(item => {

            categoriesSet.add(item.legenda);

            let geo = JSON.parse(item.geom);

            L.geoJSON(geo, {
                pointToLayer: function (feature, latlng) {
                    return L.circleMarker(latlng, {
                        radius: 2,
                        color: getColor(item.legenda),
                        fillOpacity: 0.6
                    });
                }
            }).addTo(allGridLayer);

        });

        allGridLayer.addTo(map);

        createLegend();
        createLayerSidebar(); 

        updateCharts(allGridData);

    });

    // filter kabupaten
    function loadKabupatenFilter(kabupaten) {

    fetch('/filter-kabupaten/' + encodeURIComponent(kabupaten))
    .then(res => res.json())
    .then(data => {

        if (desaLayer) map.removeLayer(desaLayer);

        desaLayer = L.geoJSON(data).addTo(map);

        map.flyToBounds(desaLayer.getBounds(), {
            duration: 1.5
        });
    });

    }

    // filter kecamatan
    function loadKecamatanFilter(kecamatan) {

        fetch('/filter-kecamatan/' + encodeURIComponent(kecamatan))
        .then(res => res.json())
        .then(data => {

            if (desaLayer) map.removeLayer(desaLayer);

            desaLayer = L.geoJSON(data).addTo(map);

            map.flyToBounds(desaLayer.getBounds(), {
                duration: 1.5
            });
        });

    }
    
    // filter desa
    function loadDesaFilter(desa) {

    // HAPUS LAYER LAMA
    if (allDesaLayer) map.removeLayer(allDesaLayer);
    if (desaLayer) map.removeLayer(desaLayer);
    if (gridLayer) map.removeLayer(gridLayer);

    // =================
    // POLYGON
    // =================
    fetch('/filter-desa/' + encodeURIComponent(desa))
    .then(res => res.json())
    .then(data => {

        desaLayer = L.geoJSON(data, {
            style: {
                color: "#2563eb",
                weight: 3,
                fillColor: "#93c5fd",
                fillOpacity: 0.4
            }
        }).addTo(map);

        map.flyToBounds(desaLayer.getBounds(), {
            duration: 1.5
        });

    });

    // =================
    // TITIK MASUK SINI
    // =================
    fetch('/grid-desa/' + encodeURIComponent(desa))
    .then(res => res.json())
    .then(data => {

        gridLayer = L.layerGroup();

        data.forEach(item => {

            let geo = JSON.parse(item.geom);

            L.geoJSON(geo, {
                pointToLayer: function (feature, latlng) {
                    return L.circleMarker(latlng, {
                        radius: 4,
                        color: getColor(item.legenda),
                        fillOpacity: 0.8
                    });
                }
            }).addTo(gridLayer);

        });

        gridLayer.addTo(map);

    });

    }


    document.addEventListener("DOMContentLoaded", function() {

    // load data kabupaten
    fetch('/kabupaten')
        .then(res => res.json())
        .then(data => {

            let select = document.getElementById("kabupatenSelect");

            select.innerHTML = '<option value="">Pilih Kabupaten</option>';

            data.forEach(item => {
                let opt = document.createElement("option");
                opt.value = item.kabkot;
                opt.text = item.kabkot;
                select.appendChild(opt);
            });

    });

    // event kabupaten
    document.getElementById("kabupatenSelect").addEventListener("change", function() {

        let kab = this.value;

        if (kab) {
            loadKabupatenFilter(kab);
        }

        // reset bawah
        document.getElementById("kecamatanSelect").innerHTML = '<option value="">Pilih Kecamatan</option>';
        document.getElementById("desaSelect").innerHTML = '<option value="">Pilih Desa</option>';

        // load kecamatan
        fetch('/kecamatan/' + encodeURIComponent(kab))
        .then(res => res.json())
        .then(data => {

            let kecSelect = document.getElementById("kecamatanSelect");

            data.forEach(item => {
                let opt = document.createElement("option");
                opt.value = item.kecamatan;
                opt.text = item.kecamatan;
                kecSelect.appendChild(opt);
            });

        });

    });

    // event kec
    document.getElementById("kecamatanSelect").addEventListener("change", function() {

    let kec = this.value;

    console.log("Kecamatan dipilih:", kec); 

    if (!kec) return;

    // ZOOM KECAMATAN
    loadKecamatanFilter(kec);

    // LOAD DESA
    fetch('/desa-list/' + encodeURIComponent(kec))
    .then(res => {
        console.log("status desa:", res.status); 
        return res.json();
    })
    .then(data => {

        console.log("data desa:", data); 

        let desaSelect = document.getElementById("desaSelect");

        desaSelect.innerHTML = '<option value="">Pilih Desa</option>';

        data.forEach(item => {
            let opt = document.createElement("option");
            opt.value = item.desa;
            opt.text = item.desa;
            desaSelect.appendChild(opt);
        });

    })
    .catch(err => console.error("ERROR DESA:", err));

    });

    // event desa
    document.getElementById("desaSelect").addEventListener("change", function() {

        let desa = this.value;

        if (desa) {
            loadDesaFilter(desa);
        }

    });

    // clear filter
    function clearFilter() {

        if (desaLayer) map.removeLayer(desaLayer);
        if (gridLayer) map.removeLayer(gridLayer);

        if (allDesaLayer) allDesaLayer.addTo(map);
        if (allGridLayer) allGridLayer.addTo(map);

        document.getElementById("kabupatenSelect").value = "";
        document.getElementById("kecamatanSelect").innerHTML = '<option value="">Pilih Kecamatan</option>';
        document.getElementById("desaSelect").innerHTML = '<option value="">Pilih Desa</option>';

        updateCharts(allGridData);

        map.flyTo([-0.5, 110], 7, {
            duration: 1.5
        });
    }

    });


    // fungsi legenda
    function createLegend() {

        legend = L.control({ position: "bottomright" });
        

        legend.onAdd = function () {
            var div = L.DomUtil.create("div", "info legend");

            div.id = "legendBox";

            div.style.background = "white";
            div.style.padding = "10px";
            div.style.borderRadius = "8px";
            div.style.maxHeight = "150px";
            div.style.overflowY = "auto";

            categoriesSet.forEach(cat => {
                div.innerHTML +=
                    '<div style="margin-bottom:5px;">' +
                    '<span style="background:' + getColor(cat) + ';width:10px;height:10px;display:inline-block;margin-right:5px;"></span>' +
                    cat +
                    '</div>';
            });

            return div;
        };

        legend.addTo(map);
    }

    document.getElementById("selectAll").addEventListener("change", function() {

        let checked = this.checked;

        document.querySelectorAll(".layerCheckbox").forEach(cb => {
            cb.checked = checked;
        });

        updateLayerFromSidebar();
    });

    function createLayerSidebar() {

        let container = document.getElementById("layerList");
        container.innerHTML = "";

        categoriesSet.forEach(cat => {

            let div = document.createElement("div");

            div.innerHTML = `
                <input type="checkbox" checked value="${cat}" class="layerCheckbox">
                <span style="color:${getColor(cat)}">${cat}</span>
            `;

            container.appendChild(div);
        });

    }

    document.addEventListener("change", function(e) {

        if (e.target.classList.contains("layerCheckbox")) {
            updateLayerFromSidebar();
        }

    });

    function updateLayerFromSidebar() {

        if (isUpdating) return; // STOP LOOP
        isUpdating = true;

        let checked = [];

        document.querySelectorAll(".layerCheckbox:checked").forEach(cb => {
            checked.push(cb.value);
        });

        if (allGridLayer) {
            map.removeLayer(allGridLayer);
        }

        allGridLayer = L.layerGroup();

        let filteredData = [];

        allGridData.forEach(item => {

            if (!checked.includes(item.legenda)) return;

            filteredData.push(item);

            let geo = JSON.parse(item.geom);

            L.geoJSON(geo, {
                pointToLayer: function (feature, latlng) {
                    return L.circleMarker(latlng, {
                        radius: 2,
                        color: getColor(item.legenda),
                        fillOpacity: 0.6
                    });
                }
            }).addTo(allGridLayer);

        });

        allGridLayer.addTo(map);

        updateCharts(filteredData);

        isUpdating = false; // buka kunci
    }

    function showSection(section) {

        document.getElementById("tutupanSection").style.display = "none";

        if (section === "tutupan") {
            document.getElementById("tutupanSection").style.display = "block";
        }
    }

    

    // pie chart
    function createPieChart() {

        let counts = {};

        allGridData.forEach(item => {
            counts[item.legenda] = (counts[item.legenda] || 0) + 1;
        });

        let labels = Object.keys(counts);
        let values = Object.values(counts);
        let colors = labels.map(l => getColor(l));

        new Chart(document.getElementById("pieChart"), {
            type: "pie",
            data: {
                labels: labels,
                datasets: [{
                    data: values,
                    backgroundColor: colors
                }]
            }
        });
    }


    function updateCharts(data) {

        console.log("Data chart:", data.length);

        let counts = {};

        data.forEach(item => {
            counts[item.legenda] = (counts[item.legenda] || 0) + 1;
        });

        let labels = Object.keys(counts);
        let values = Object.values(counts);
        let colors = labels.map(l => getColor(l));

        // destroy chart lama
        if (window.pieChart && typeof window.pieChart.destroy === "function") {
                window.pieChart.destroy();
        }

        if (window.barChart && typeof window.barChart.destroy === "function") {
            window.barChart.destroy();
        }

        // PIE
        window.pieChart = new Chart(document.getElementById("pieChart"), {
            type: "pie",
            data: {
                labels: labels,
                datasets: [{
                    data: values,
                    backgroundColor: colors
                }]
            }
        });


    }


    function toggleSidebar() {
        let sidebar = document.getElementById("sidebar");
        let btn = document.getElementById("toggleBtn");

        sidebar.classList.toggle("closed");

        if (sidebar.classList.contains("closed")) {
            btn.style.left = "10px";
        } else {
            btn.style.left = "300px";
        }
    }

    var legendButton = L.control({ position: 'topright' });

    legendButton.onAdd = function () {
        var div = L.DomUtil.create('div');

        var btn = L.DomUtil.create('button', '');
        btn.innerHTML = "Legend";
        btn.className = "bg-white px-3 py-1 rounded-lg shadow text-sm";

        btn.onclick = function () {
            let legendBox = document.getElementById("legendBox");

            if (!legendBox) return;

            if (legendBox.style.display === "none") {
                legendBox.style.display = "block";
            } else {
                legendBox.style.display = "none";
            }
        };

        div.appendChild(btn);
        return div;
    };

    legendButton.addTo(map);

    
</script>

</body>
</html>