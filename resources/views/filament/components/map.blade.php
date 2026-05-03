<div id="map" style="height: 300px;"></div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    const lat = {{ $getRecord()->check_in_latitude }};
    const lng = {{ $getRecord()->check_in_longitude }};

    const map = new google.maps.Map(document.getElementById('map'), {
      zoom: 18,
      center: {
        lat: lat,
        lng: lng
      },
    });

    new google.maps.Marker({
      position: {
        lat: lat,
        lng: lng
      },
      map: map,
    });
  });
</script>
