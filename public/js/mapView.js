////////// AJAX 1 : GET ZIPCODE FROM DATABASE /////////////

function kakaoMap(lat,long) {
    var mapContainer = document.getElementById('map'),
        mapOption = { 
        center: new kakao.maps.LatLng(lat, long), // center point of map
        level: 3 // map zoom level
    };

    var map = new kakao.maps.Map(mapContainer, mapOption); // create map

    // location of map marker
    var markerPosition  = new kakao.maps.LatLng(lat, long); 

    // create map marker
    var marker = new kakao.maps.Marker({
        position: markerPosition
    });

    // display map marker
    marker.setMap(map);
}



// async function zipcodeGeotagging(zipcode) {
//     const url = "https://geocode.xyz/" + zipcode + "?region=KR&json=1"
   
//     let response = await fetch(url);
//     let coordinates = await response.json();
//     console.log(coordinates)
//     let lat = coordinates['latt']
//     let long = coordinates['longt']
    
//     kakaoMap(lat,long)
// }
