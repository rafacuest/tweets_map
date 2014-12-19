<!DOCTYPE html>
<html>
<head>
	<title>
		
	</title>
	<meta charset="utf-8" />
	<meta name="description" content="">
	<meta name="keywords" content="">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<!-- del leafletjs -->
	<link rel="stylesheet" href="http://leafletjs.com/dist/leaflet.css" />
	<!-- del markercluster -->
	<link href='//api.tiles.mapbox.com/mapbox.js/plugins/leaflet-markercluster/v0.4.0/MarkerCluster.css' rel='stylesheet' />
	<link href='//api.tiles.mapbox.com/mapbox.js/plugins/leaflet-markercluster/v0.4.0/MarkerCluster.Default.css' rel='stylesheet' />
	<!--[if lte IE 8]><link rel="stylesheet" href="/static/mapa/js/leaflet.markercluster/MarkerCluster.Default.ie.css" /><![endif]-->
	<!-- deleditor de poligonos del leaftlet  -->
	<link rel="stylesheet" href="http://leaflet.github.io/Leaflet.draw/leaflet.draw.css"/>
	<!-- Bootstrap 3.1.1  -->
	<link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css"/>
	<link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap-theme.min.css" />
	<!-- Própios -->
	<link rel="stylesheet" href="styles.css" />
</head>
<body>
	<div id="map">
	
	</div>
	<div id="col_lateral">
		<ul id="lista_tweets">
			
		</ul>
	</div>
	<span class="break"></span>
	<!-- del leaflet -->
	<script src="http://leafletjs.com/dist/leaflet.js"></script>
	<!-- del markercluster -->
	<script src='//api.tiles.mapbox.com/mapbox.js/plugins/leaflet-markercluster/v0.4.0/leaflet.markercluster.js'></script>
	<!-- del jquery para el bootstrap  -->
	<script type="text/javascript" src="http://code.jquery.com/jquery-1.8.3.min.js" charset="UTF-8"></script>
	<!-- del bootstrap para el bootstrap  -->
	<script src="//netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"></script>
	<!-- deleditor de poligonos del leaftlet  -->
	<script src="http://leaflet.github.io/Leaflet.draw/leaflet.draw.js"></script>
	
	<script type="text/javascript">
		var lng = "";
		var lat = "";
		var rad = "100km";
		var hashtag = ""; 
		
		//Obtener la posicion inicial
		function getLocation() {
			if (navigator.geolocation) {
				navigator.geolocation.getCurrentPosition(showPosition,showError);
			} else {
				//alert("Geolocation is not supported by this browser.");
			}
		}//Fin getLocation
		
		function showPosition(position) {
			lng = position.coords.longitude;
			lat = position.coords.latitude;
			rad = "100km";
			//L.marker([lat,lng ]).addTo(map);
			getTweet();
		}//Fin showPosition
		
		function showError(error) {
			switch(error.code) {
				case error.PERMISSION_DENIED:
					//alert("User denied the request for Geolocation.");
					break;
				case er ror.POSITION_UNAVAILABLE:
					//alert("Location information is unavailable.");
					break;
				case error.TIMEOUT:
					//al ert("The request to get user location timed out.");
					break;
				case error.UNKNOWN_ERROR:
					//alert("An unknown error occurred.");
					break;
			}
		}//Fin showError
		
		var map = L.map('map');
		southwest = new L.LatLng(43.4210,-9.1406);			//Centrar el mapa a un rectangulo con el max zoom possible
		northeast = new L.LatLng(35.4965,6.1523);
		bounds = new L.LatLngBounds(southwest, northeast);
		map.fitBounds(bounds);
		
		//Cajetilla del buscador
		var info = L.control();
		info.onAdd = function (map) {
			this._div = L.DomUtil.create('div', 'info');
			this.update();
			return this._div;
		};
		info.update = function (props) {
			this._div.innerHTML = '<input class="form-control" id="inputHashTag" type="text" value="" />'
			;
		};
		info.addTo(map);
		//Desactiva los eventos sobre el cajetin de busqueda
		info.getContainer().addEventListener('mouseover', function () {
			map.dragging.disable();
			map.doubleClickZoom.disable(); 
		});
		//Activa los eventos sobre el cajetin de busqueda
		info.getContainer().addEventListener('mouseout', function () {
			map.dragging.enable();
			map.doubleClickZoom.enable(); 
		});
		
		//Mapas
		var MB_URL='http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';
		//var MB_URL = 'http://{s}.tiles.mapbox.com/v3/{id}/{z}/{x}/{y}.png';
		var Esri_WorldImagery = L.tileLayer('http://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
			attribution: 'Tiles &copy; Esri &mdash; Source: Esri, i-cubed, USDA, USGS, AEX, GeoEye, Getmapping, Aerogrid, IGN, IGP, UPR-EGP, and the GIS User Community'
		});
		var OpenMapSurfer_Grayscale = L.tileLayer('http://openmapsurfer.uni-hd.de/tiles/roadsg/x={x}&y={y}&z={z}', {
			minZoom: 0,
			maxZoom: 19
		});
		//L.tileLayer('http://{s}.www.toolserver.org/tiles/bw-mapnik/{z}/{x}/{y}.png').addTo(map);
		OpenMapSurfer_Grayscale.addTo(map);
		
		var markers = new L.MarkerClusterGroup();
		// Editor de circulos
		var drawnItems = new L.FeatureGroup();
        map.addLayer(drawnItems);

        var drawControl = new L.Control.Draw({
            draw: {
				polygon: false,
				marker: false,
				rectangle: false,
				polyline: false,
				circle:{
					shapeOptions: {
						color: 'red'
					}
				}
			}
        });
        map.addControl(drawControl);

        map.on('draw:drawstart', function (e) {
        	drawnItems.clearLayers() 
        });//Fin draw:created
        
        map.on('draw:created', function (e) {
            var type = e.layerType,
            layer = e.layer;
            drawnItems.addLayer(layer);
            lng = e.layer._latlng.lng;
			lat = e.layer._latlng.lat;
			rad = Math.round(e.layer._mRadius/1000) +"km";
			hashtag = $('#inputHashTag').val();
			animacionCirculo( lat, lng, e.layer._mRadius );
			getTweet();
        });//Fin draw:created
		
        var pid; 
        var circle1;
		var circle3;
		function animacionCirculo(lat, lng, maxRadio){
			circle1 = L.circle([lat, lng], 0).addTo(map);
			var contador = 0;
			
        	pid = window.setInterval(function(){
        			
				if( circle1.getRadius() < maxRadio ){
					circle1.setRadius(circle1.getRadius() + Math.floor(maxRadio/30));
					circle1.redraw();
				}
				else{
					circle1.setRadius(0);
				}
			}, 50);
			
		}//Fin animacionCirculo
        
        //evento intro de la cajetilla de busqueda
        $('#inputHashTag').keyup(function(e){
			if(e.keyCode == 13){
				hashtag = $('#inputHashTag').val();
				getTweet();
				}
			});
		
        //evento ready	
		$(document).ready(function() {
			getLocation();
		});//Fin document ready
		
		//obtenet y mostrar los tweets
		function getTweet() {
			if (typeof markers != "undefined"){
				markers.clearLayers();
				}
			$.getJSON("webservice.php",
				{format: "json",
				lng: lng,
				lat: lat,
				rad: rad,
				hashtag: hashtag
				}, 
				function (json) {
					//limpiar la lista lateral
					$("#lista_tweets").empty();
					if( json.error == 'Rate limit exceeded') {
						//alert(json.error);
						alert("Upsss demasiadas peticiones en tan poco tiempo!\nVuelve a intentarlo mas tarde ");
					}
					$.each(json, function (i, tweet) {
						var latlng = L.latLng(tweet.lng,tweet.lat);
						var icon = L.divIcon({
							className: 'map-marker',
							iconSize:null,
							html:'<img class="profile_image" src="'+tweet.profile_image_url+'" />',
						});
						var marker = L.marker(latlng, { icon: icon,title: tweet.text,id_str:tweet.id_str });
						
						marker.on('click', function (e){
							var aTag = $("a[name='"+ e.target.options.id_str +"']");
							desplazamiento = aTag.offset().top + $("ul#lista_tweets").scrollTop(); //desplazamiento del item respecto al top menos la posición en donde se encuentra el scroll actualmente
							$("ul#lista_tweets").animate({ scrollTop:desplazamiento}, 1000);
						});
						markers.addLayer( marker );
						map.addLayer(markers);
						//actualizar lista lateral
						$("#lista_tweets").append(
							'<li>'+
								'<a name="'+tweet.id_str+'"/>'+
								'<div style="background-image:url(\''+tweet.profile_background_image_url+'\')">'+
								'<a href="https://twitter.com/'+tweet.user+'" target="_blank"><img src="'+tweet.profile_image_url+'" style="float:left"/></a>'+
									'<h3><a href="https://twitter.com/'+tweet.user+'/status/'+tweet.id_str+'" target="_blank">'+tweet.user+'</a></h3>'+
								'</div>'+
								'<p>'+tweet.created_at+'</p>'+
								'<p>'+tweet.text+'</p>'+
							'</li>'
							);
						});
					
						//evento clicl lista lateral se debe poner aqui por que si no no coge los eventos de la lista rellenada
						$("#lista_tweets li").on('click',function(e){
							
							if(event.target.nodeName == 'A'){		//si se clica desde el enlace del nombre de usuario o de la imagen no hagas el zoom
								event.stopPropagation();
								}
							else{
								
								id_str = jQuery(this).find("a").attr("name");
								markers.eachLayer(function (layer) {
									if(layer.options.id_str == id_str ){
										//map.panTo([layer.getLatLng().lat,layer.getLatLng().lng ]);
										var southWest = L.latLng(layer.getLatLng().lat, layer.getLatLng().lng),
										northEast = L.latLng(layer.getLatLng().lat,layer.getLatLng().lng),
										bounds = L.latLngBounds(southWest, northEast);
										map.fitBounds(bounds);
										}
								});
							}	
						});
						//Limpia el trhead de la animación del circulo
						if (pid>0){
							window.clearInterval(pid);
							map.removeLayer(circle1);
							map.removeLayer(circle2);
							map.removeLayer(circle3);
						}
					}
				);
			}//Fin getTweet
	</script>
	<script>
	(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
	(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
	m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
	})(window,document,'script','//www.google-analytics.com/analytics.js','ga');
	
	ga('create', 'UA-47508055-3', 'auto');
	ga('send', 'pageview');
	
	</script>
</body>
