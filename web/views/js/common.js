$(function() {
    var navbtn = $('#show_category')
    catList = $('#category-list')
    mapShadow = $('#mapshadow')

// navbtn.click(function(event){
//  event.preventDefault();
//  navbtn.toggleClass('active')
//  catList.toggle()
//  mapShadow.toggle()
// })

// mapShadow.click(function(){
//  navbtn.toggleClass('active')
//  catList.toggle()
//  mapShadow.toggle()
// })
    map = new GMaps({
        div: '#map_inner',
        lat: -12.043333,
        lng: -77.028333
    });

    myLt = 0,
        myLn = 0

    new GMaps.geolocate({
        success: function(position) {
            myLt = position.coords.latitude,
                myLn = position.coords.longitude
            console.log(position.coords.latitude);
            console.log(position.coords.longitude);
            map.setCenter(position.coords.latitude, position.coords.longitude);
            map.addMarker({
                lat: position.coords.latitude,
                lng: position.coords.longitude,
                icon : {
                    size : new google.maps.Size(26, 26),
                    url : '/img/map-mark.png'
                },
                infoWindow: {
                    content: '<p>My position</p>'
                }
            });

        },
        always: function() {
            // alert("Done!");
        }
    });

    map.addControl({
        position: 'top_right',
        content: 'Geolocate',
        style: {
            margin: '5px',
            padding: '1px 6px',
            border: 'solid 1px #717B87',
            background: '#fff'
        }
    });

    function loadResults (data) {
        var items, markers_data = [];
        if (data.venues.length > 0) {
            items = data.venues;

            for (var i = 0; i < items.length; i++) {
                var item = items[i];

                if (item.location.lat != undefined && item.location.lng != undefined) {
                    var icon = '/img/map-mark.png';

                    markers_data.push({
                        lat : item.location.lat,
                        lng : item.location.lng,
                        title : item.name,
                        icon : {
                            size : new google.maps.Size(30, 30),
                            url : icon
                        }
                    });
                }
            }
        }

        map.addMarkers(markers_data);
    }

    var xhr = $.getJSON('http://coffeemaker.herokuapp.com/foursquare.json?q[near]=Lima,%20PE&q[query]=Ceviche');

    xhr.done(loadResults);
})