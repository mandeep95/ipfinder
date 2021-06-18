<?php
    $IP = $_SERVER['REMOTE_ADDR'];
    $ip = htmlentities($_GET["wp-admin-ip"]);
     
    // You may want to use a parameter with a more friendly name than "wp-admin-ip"
    $hostname = gethostbyaddr($_GET['wp-admin-ip']);
     
    $location = json_decode(file_get_contents('http://api.ipstack.com/'.$ip.'?access_key=YOUR_IPSTACK_API_KEY&format=1'));
    $details = json_decode(file_get_contents("http://ipinfo.io/{$ip}/json"));
    $address = "";
    if(isset($_GET['wp-admin-ip'])) { 
        $self_url = htmlspecialchars($_SERVER["REQUEST_URI"]);
        echo '<form method="get" action="' . $self_url . '#detailed_information">';
        foreach($_GET as $name => $value) {
            $name = htmlspecialchars($name);
            $value = htmlspecialchars($value);
            if($name !== "wp-admin-ip") {
                echo '<input type="hidden" name="'. $name .'" value="'. $value .'">';
            }
        }
         
        $address = $location->latitude . " " . $location->longitude;
        echo '<input autocomplete="off" type="text" name="wp-admin-ip" id="wp-admin-ip" maxlength="15" placeholder="Enter IP Address Here" style="width:100%; background:#EEF;" onkeyup="wp_admin_ip_success()" /><br/><br/>
        <input type="submit" class="button" value="Lookup IP Address" disabled id="wp-admin-ip-button" />
        </form>';
         
        echo "<br><h2><a name='detailed_information' style='text-decoration: none; pointer-events: none; color: #000000;'>Detailed IP Information</a> Via <a href='https://ipstack.com/' rel='noopener noreferrer' target='_blank'>ipstack's</a> API</h2>";
        echo "<b>IP Address: </b>" .$location->ip;
        echo "<br><b>IP Type: </b>" .$location->type;
        echo "<br><b>Continent Name: </b>" .$location->continent_name;
        echo "<br><b>Country Code: </b>" .$location->country_code;
        echo "<br><b>Country Name: </b>" .$location->country_name;
        echo "<br><b>City: </b>" .$location->city;
        echo "<br><b>State/Region: </b>" .$location->region_name;
        echo "<br><b>Region Code: </b>" .$location->region_code;
        echo "<br><b>Zip/Postal Code: </b>" .$location->zip;
        echo "<br><b>Hostname: </b>" .$hostname;
        echo "<br><b>Organization: </b>" .$details->org;
        echo "<br><b>Latitude: </b>" .$location->latitude;
        echo "<br><b>Longitude: </b>" .$location->longitude;
        echo "<br><b>Your Browser User-Agent String: </b>" .$_SERVER['HTTP_USER_AGENT'];
         
        echo "<br><br><h2>Summarized IP Information Via The API From <a href='https://ipinfo.io/' rel='noopener noreferrer' target='_blank'>ipinfo.io</a></h2>";
        echo "<b>IP Address: </b>" .$details->ip;
        echo "<br><b>Country Code: </b>" .$details->country;
        echo "<br><b>City: </b>" .$details->city;
        echo "<br><b>State/Region: </b>" .$details->region;
        echo "<br><b>Zip/Postal Code: </b>" .$details->postal;
        echo "<br><b>Hostname: </b>" .$details->hostname;
        echo "<br><b>Organization: </b>" .$details->org;
        echo "<br><b>Location (Latitude and Longitude): </b>" .$details->loc;
    }
    else {
        $self_url = htmlspecialchars($_SERVER["REQUEST_URI"]);
        print ('<form method="get" action="' . $self_url . '#detailed_information">');
        foreach($_GET as $name => $value) {
            $name = htmlspecialchars($name);
            $value = htmlspecialchars($value);
            if($name !== "wp-admin-ip") {
                echo '<input type="hidden" name="'. $name .'" value="'. $value .'">';
            }
        }
        print ('<input autocomplete="off" type="text" name="wp-admin-ip" id="wp-admin-ip" maxlength="15" onkeyup="wp_admin_ip_success()" placeholder="Enter IP Address Here" value="'.$IP.'" style="width:100%; background:#EEF;" /><br/><br/>
        <input type="submit" class="button" value="Lookup IP Address" disabled id="wp-admin-ip-button" />
        </form>');
    }
?>
 
<script type="text/javascript">
    function wp_admin_ip_success() {
        ipaddress = document.getElementById("wp-admin-ip").value;
        if(ipaddress.length === 0 || !ipaddress.trim() || !ValidateIPaddress(ipaddress.trim())) { 
            document.getElementById('wp-admin-ip-button').disabled = true; 
        }
        else { 
            document.getElementById('wp-admin-ip-button').disabled = false;
        }
    }
    wp_admin_ip_success();
     
    function ValidateIPaddress(ipaddress) {
        if (/^(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/.test(ipaddress)) {
            return (true)
        }
        return (false)
    }
</script>
 
<?php
    function geocode($address) {
        $address = urlencode($address);
         
        // Get a Google Maps key from the Google Developers Console
        // This is API key is not referrer restricted. It is meant for server-side requests. 
        $url = "https://maps.google.com/maps/api/geocode/json?address={$address}&key=YOUR_GOOGLE_MAPS_API_KEY_ONE";
         
        $resp_json = file_get_contents($url);
        $resp = json_decode($resp_json, true);
        if($resp['status']=='OK'){
            $lati = $resp['results'][0]['geometry']['location']['lat'];
            $longi = $resp['results'][0]['geometry']['location']['lng'];
            $formatted_address = $resp['results'][0]['formatted_address'];
            if($lati && $longi && $formatted_address) {
                $data_arr = array();
                array_push(
                    $data_arr, 
                    $lati, 
                    $longi, 
                    $formatted_address
                );
                return $data_arr;
            }
            else {
                return false;
            }
        }
        else {
            return false;
        }
    }
?>
 
<?php
    if(preg_match('/\S/', $address)) {
        $data_arr = geocode($address);
        if($data_arr) {
            echo "<br><br><h2>Geolocation (Based On Data From ipstack)</h2>";
            $latitude = $data_arr[0];
            $longitude = $data_arr[1];
            $formatted_address = $data_arr[2];
?>
 
<style>
    /* Always set the map height explicitly to define the size of the div element that contains the map. */
    #map {
        width:100%;
        height:30em;
    }
</style>
 
<a name='map_element_or_error' style='text-decoration: none; pointer-events: none; color: #000000; font-size: 1px;'></a><div id="map">Loading map...</div>
 
<!-- This second Google Maps API key used below is referrer restricted key. It is meant specifically for JavaScript browser requests. -->
<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=YOUR_GOOGLE_MAPS_API_KEY_TWO"></script>
 
<script>
    function init_map() {
        var myOptions = {
            zoom: 14,
            center: new google.maps.LatLng(<?php echo $latitude; ?>, <?php echo $longitude; ?>),
            mapTypeId: google.maps.MapTypeId.ROADMAP
        };
        map = new google.maps.Map(document.getElementById("map"), myOptions);
        marker = new google.maps.Marker({
            map: map,
            position: new google.maps.LatLng(<?php echo $latitude; ?>, <?php echo $longitude; ?>)
        });
        infowindow = new google.maps.InfoWindow({
            content: "<?php echo $formatted_address; ?>"
        });
        google.maps.event.addListener(marker, "click", function () {
            infowindow.open(map, marker);
        });
        infowindow.open(map, marker);
    }
    google.maps.event.addDomListener(window, 'load', init_map);
</script>
 
<?php
        }
        else {
            echo "<a name='map_element_or_error' style='text-decoration: none; pointer-events: none; color: #000000;'><span style='color: red;'>ERROR: No map found!</span></a>";
        }
    }
?>