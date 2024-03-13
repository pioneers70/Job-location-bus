<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Маршруты Красноярск-Кызыл</title>
</head>
<link rel="stylesheet" href="mapstyle.css">

<script src="mapscript.js"></script>
<script src="sweetalert.js"></script>

<script>


    $(document).ready(function () {

        /** Координаты остановок и сами остановки ------------------------------------------------------------------ */

        let pointsStation = [];
        let track = [];
        let busposition = []
        Swal.mixin({
            customClass: {
                confirmButton: 'btn btn-primary',
                cancelButton: 'btn btn-outline-danger'
            },
            buttonsStyling: false
        }).fire({
            html: '<div class="loading-wave">' +
                '<div class="loading-bar"></div>' +
                '<div class="loading-bar"></div>' +
                '<div class="loading-bar"></div>' +
                '<div class="loading-bar"></div>' +
                '</div>',
            title: 'Пожалуйста, ожидайте, выполняется загрузка данных о рейсе',
            text: 'Пожалуйста, ожидайте, выполняется загрузка данных о рейсе',
            showConfirmButton: false,
            allowOutsideClick: false,
            onBeforeOpen: () => {
                Swal.showLoading();
            }
        });
        fetch('getdata.php?idTrip=1669478')
            .then(async response => await response.json())
            .then(async data => {
                Swal.close();
                console.log(data);
                data.datapoint.forEach(point => {
                    let newPoint = {
                        id: point.idstation,
                        name: point.stationName,
                        timearr: point.timearrft,
                        timesend: point.timesendft,
                        timearrplan: point.timearrpl,
                        timesendplan: point.timesendpl,
                        cords: [point.longitude, point.latitude]
                    };
                    pointsStation.push(newPoint);
                });
                data.pointtrip.forEach(pointtr => {
                    let trackpoint = {
                        points: [pointtr.longtrip, pointtr.latitrip]
                    };
                    track.push(trackpoint);
                });
                data.busposition.forEach(geobus => {
                    let buscoord = {
                        cords: [geobus.buslongitude, geobus.buslatitude]
                    };
                    busposition.push(buscoord);
                });
                while (true) {
                    if (pointsStation.length > 0) {
                        console.log('Массив pointsStation заполнен', pointsStation, track);
                        break;
                    } else {
                        console.log('Массив пуст. ожидайте..')
                    }
                    await new Promise(resolve => setTimeout(resolve, 5000));
                }

                /*                function copyCord(vP, startP, endP) {
                                    let res = [];
                                    let j = 0;
                                    for (let i = startP; i < endP; i++) {
                                        res[j] = ol.proj.transform(vP[i], 'EPSG:4326', 'EPSG:3857');
                                        j++;
                                    }
                                    return res;
                                }*/

                /** Точки прохождения всего маршрута и отрисовка его -------------------------------------------------*/
                let vectorSource = new ol.source.Vector();
                let routePoints = [];
                track.forEach(point => {
                    routePoints.push(ol.proj.fromLonLat(point.points));
                });
                let routeLine = new ol.geom.LineString(routePoints);
                let routeFeature = new ol.Feature({
                    geometry: routeLine
                });
                routeFeature.setStyle(new ol.style.Style({
                    fill: new ol.style.Fill({color: '#6A5ACD', weight: 1.5}),
                    stroke: new ol.style.Stroke({color: '#6A5ACD', width: 5})
                }));
                vectorSource.addFeature(routeFeature);

                /** уже пройденный путь-------------------------------------------------------------------------------*/


                let iconFeatures = [];
                let labelFeatures = [];
                let iconSource, labelSource;
                let busFeatures = [];
                let iconBusSource;

                let posbus = [{name: '619 Красноярск - Кызыл', cords: [92.0536783225959, 56.2197483205748]}];
                let busCoords = posbus[0].cords;

                /** Отрисовка позиции автобуса ---------------------------------------------------------------------- */

                function addMarkerBus() {
                    for (let j = 0; j < busposition.length; j++) {
                        let busFeature = new ol.Feature({
                            geometry: new ol.geom.Point(ol.proj.transform(busposition[j].cords, 'EPSG:4326', 'EPSG:3857')),
                            name: busposition[j].name,
                            id: 'bus' + busposition[j].id

                        });
                        let busStyle = new ol.style.Style({
                            image: new ol.style.Icon({
                                scale: 0.5,
                                opacity: 0.9,
                                src: "https://cdn1.ozonusercontent.com/s3/marketing-api/banners/em/ew/c96/emewn0ujuN78mxqrY0ihpjaY6RjKqA0N.png"
                            })
                        });
                        busFeature.setStyle([busStyle]);
                        busFeatures.push(busFeature);

                    }
                    iconBusSource = new ol.source.Vector({features: busFeatures});
                }


                /** отрисовка иконки остановки -----------------------------------------------------------------------*/


                function addMarkerStations() {
                    for (let j = 0; j < pointsStation.length; j++) {
                        let iconFeature = new ol.Feature({
                            geometry: new ol.geom.Point(ol.proj.transform(pointsStation[j].cords, 'EPSG:4326', 'EPSG:3857')),
                            name: pointsStation[j].name,
                            timearr: pointsStation[j].timearr,
                            timesend: pointsStation[j].timesend,
                            id: 'station-' + pointsStation[j].id
                        });
                        let iconStyle;
                        if (pointsStation[j].timesend && pointsStation[j].timearr) {
                            let timearrMinutes = timeToMinutes(pointsStation[j].timearr);
                            let timesendMinutes = timeToMinutes(pointsStation[j].timesend);
                            let stopDuration = timesendMinutes - timearrMinutes;

                            if (stopDuration > 0) {
                                iconStyle = new ol.style.Style({
                                    image: new ol.style.Icon({
                                        scale: 0.25,
                                        opacity: 1,
                                        src: "https://upload.wikimedia.org/wikipedia/commons/9/97/GO_bus_symbol.svg"
                                    })
                                });
                            } else {
                                iconStyle = new ol.style.Style({
                                    image: new ol.style.Icon({
                                        scale: 0.2,
                                        opacity: 0.7,
                                        src: "https://upload.wikimedia.org/wikipedia/commons/9/97/GO_bus_symbol.svg"
                                    })
                                });
                            }
                        } else {
                            // Если нет времени остановки, используем другую иконку
                            iconStyle = new ol.style.Style({
                                image: new ol.style.Icon({
                                    scale: 0.28,
                                    opacity: 0.8,
                                    src: "https://school.stnorbert.org/wp-content/uploads/2018/01/bus-stop-filled-100.png"
                                })
                            });
                        }

                        iconFeature.setStyle([iconStyle]);
                        iconFeatures.push(iconFeature);
                    }
                    iconSource = new ol.source.Vector({features: iconFeatures});
                }

                /** Добавление названия ----------------------------------------------------------------------------- */

                function addLabelStations() {
                    for (let j = 0; j < pointsStation.length; j++) {
                        let labelFeature = new ol.Feature({
                            geometry: new ol.geom.Point(ol.proj.transform(pointsStation[j].cords, 'EPSG:4326', 'EPSG:3857')),
                            name: pointsStation[j].name,
                            timearr: pointsStation[j].timearr,
                            timesend: pointsStation[j].timesend,
                            id: 'station-' + pointsStation[j].id
                        });
                        let labelStyle = new ol.style.Style({
                            text: new ol.style.Text({
                                font: '12px Arial',
                                text: pointsStation[j].name,
                                overflow: true,
                                fill: new ol.style.Fill({
                                    color: '#000'
                                }),
                                stroke: new ol.style.Stroke({
                                    color: '#000',
                                    width: 0.5
                                }),
                                textBaseline: 'bottom',
                                textAlign: 'top',
                                rotation: 0,
                                offsetY: -15
                            })
                        });
                        labelFeature.setStyle([labelStyle]);
                        labelFeatures.push(labelFeature);
                    }
                    labelSource = new ol.source.Vector({
                        features: labelFeatures
                    });
                }

                addMarkerBus();
                addLabelStations();
                addMarkerStations();

                let clusterSource = new ol.source.Cluster({
                    /**
                     радиус, после которого близко находящиеся объекты
                     объединяются и считаются
                     */
                    distance: 40,
                    /**
                     * Координаты самих объектов
                     */
                    source: labelSource,
                });
                let styleCache = {};

                /** Создаем слой Кластер и задаем для него стили------------------------------------------------------*/

                let clusters = new ol.layer.Vector({
                    source: clusterSource,
                    style: function (feature) {
                        let size = feature.get('features').length;
                        let style = styleCache[size];
                        if (!style && size >= 2) {
                            style = [new ol.style.Style({
                                image: new ol.style.Circle({
                                    radius: 20,
                                    stroke: new ol.style.Stroke({
                                        color: '#fff'
                                    }),
                                    fill: new ol.style.Fill({
                                        color: '#3399CC',
                                        // color: '#ff0000'
                                    })
                                }),
                                text: new ol.style.Text({
                                    // text: listSt,
                                    text: size.toString(),
                                    fill: new ol.style.Fill({
                                        color: '#fff'
                                    }),
                                    scale: 2
                                })
                            })];
                            styleCache[size] = style;
                        }
                        return style;
                    }
                });

                /** Здесь все слои -----------------------------------------------------------------------------------*/

                let map = new ol.Map({
                    target: 'map',
                    layers: [
                        new ol.layer.Tile({
                            source: new ol.source.OSM(),
                            name: 'map'
                        }),
                        new ol.layer.Vector({
                            source: vectorSource,
                            name: 'Road'
                        }),
                        new ol.layer.Vector({
                            source: iconSource,
                            name: 'imgStations'
                        }),
                        new ol.layer.Vector({
                            source: labelSource,
                            name: 'labelStations'
                        }),
                        /*                        new ol.layer.Vector({
                                                    source: distance,
                                                    name: 'distance'
                                                }),*/
                        new ol.layer.Vector({
                            source: iconBusSource,
                            name: 'iconBusSource'
                        }),
                        clusters
                    ],
                    overlays: [],
                    /**  renderer: 'canvas'---------------------------------------------------------------------------*/

                    view: new ol.View({
                        /** Координаты центра-------------------------------------------------------------------------*/
                        center: ol.proj.fromLonLat(busCoords),
                        zoom: 10,
                        minZoom: 5,
                        maxZoom: 25
                    })
                });

                /**  всплывающее окно---------------------------------------------------------------------------------*/
                let container = document.getElementById("popup");
                let content = document.getElementById("popup-content");
                let popupCloser = document.getElementById("popup-closer");

                let overlay = new ol.Overlay({
                    element: container,
                    autoPan: true
                });

                function handleStopPixelClick(feature, e) {
                    let coordinate = e.coordinate;
                    let name = feature.get('name');
                    let timearrString = feature.get('timearr');
                    let timedepString = feature.get('timesend');
                    let timearrplString = feature.get('timearrplan');
                    let timedepplString = feature.get('timesendplan');
                    let contentHTML = '<p>Остановка: <span style="font-weight: bold;">' + name + '</span></p>';
                    if (timearrString) {
                        contentHTML += '<p>Время прибытия: <span style="color: blue; font-weight: bold">' + timearrString + '</span></p>';
                    } else {
                        contentHTML += '<p>Автобус остановку не совершал в данном пункте</p>';
                    }
                    if (timedepString) {
                        let timearr = timeToMinutes(timearrString);
                        let timedep = timeToMinutes(timedepString);
                        let timestop = timedep - timearr;

                        contentHTML += '<p>Время отправки: <span style="color: green; font-weight: bold">' + timedepString + '</span></p>' +
                            '<p>Время стоянки:<span style="color: red; font-weight: bold">' + timestop + ' минут</span></p>';
                    }
                    content.innerHTML = contentHTML;
                    overlay.setPosition(coordinate);
                }

                function handleBusPixelClick(feature, e) {
                    let coordinate = e.coordinate;
                    let name = feature.get('name');
                    content.innerHTML = '<p>Автобус: <span style="font-weight: bold;">' + name + '</span></p>';

                    overlay.setPosition(coordinate);
                }

                function timeToMinutes(timeString) {
                    const [datePart, timePart] = timeString.split(" ");
                    const [hours, minutes] = timePart.split(":");
                    const [day, month, year] = datePart.split(".");
                    const date = new Date(year, month - 1, day, hours, minutes);
                    return date.getHours() * 60 + date.getMinutes();
                }

                map.on('click', function (e) {
                    let pixel = map.getEventPixel(e.originalEvent);
                    map.forEachFeatureAtPixel(pixel, function (feature) {
                        if (feature.get('id').startsWith('bus')) {
                            handleBusPixelClick(feature, e);
                        } else {
                            handleStopPixelClick(feature, e);
                        }
                    });
                    map.addOverlay(overlay);
                });

                popupCloser.addEventListener('click', function () {
                    overlay.setPosition(undefined);
                });
                /** Анимация маркера ---------------------------------------------------------------------------*/
                let speed = 200; // pixels per second
                let marker = new ol.Feature({
                    geometry: new ol.geom.Point(routePoints[0])
                });
                let markerStyle = new ol.style.Style({
                    image: new ol.style.Icon({
                        scale: 0.5,
                        opacity: 0.9,
                        src: "https://cdn1.ozonusercontent.com/s3/marketing-api/banners/em/ew/c96/emewn0ujuN78mxqrY0ihpjaY6RjKqA0N.png"
                    })
                });
                marker.setStyle(markerStyle);
                vectorSource.addFeature(marker);

                let startTime = Date.now();
                let moveMarker = function (event) {
                    let elapsedTime = event.frameState.time - startTime;
                    let index = Math.round(speed * elapsedTime / 1000);
                    if (index >= routePoints.length) {
                        return;
                    }
                    let currentPoint = routePoints[index];
                    marker.getGeometry().setCoordinates(currentPoint);
                    map.render();
                    if (index === routePoints.length - 1) {
                        map.un('postcompose', moveMarker);
                    }
                };

                map.on('postcompose', moveMarker);

            });
    })


</script>
<body>

<div id="map"></div>
<div id="popup" class="ol-popup">
    <a href="#" id="popup-closer" class="ol-popup-closer"></a>
    <div id="popup-content"></div>
</div>
<div class="loading-wave">
    <div class="loading-bar"></div>
    <div class="loading-bar"></div>
    <div class="loading-bar"></div>
    <div class="loading-bar"></div>
</div>
</body>

</html>
