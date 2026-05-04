<!DOCTYPE html>
<html>
<head>
    <title>Direct API Test</title>
</head>
<body>
    <h1>Direct API Test</h1>
    
    <h2>Test Provinces</h2>
    <button onclick="testProvinces()">Get Provinces</button>
    <pre id="provinces"></pre>
    
    <h2>Test Cities - Davao del Sur</h2>
    <button onclick="testCities()">Get Cities for Davao del Sur</button>
    <pre id="cities"></pre>
    
    <h2>Test Barangays - Davao City</h2>
    <button onclick="testBarangays()">Get Barangays for Davao City</button>
    <pre id="barangays"></pre>
    
    <script>
        const basePath = '<?php echo rtrim(dirname($_SERVER['SCRIPT_NAME']), '/'); ?>';
        
        function testProvinces() {
            fetch(basePath + '/api-provinces.php')
                .then(r => r.json())
                .then(data => {
                    document.getElementById('provinces').textContent = JSON.stringify(data, null, 2);
                })
                .catch(e => {
                    document.getElementById('provinces').textContent = 'Error: ' + e.message;
                });
        }
        
        function testCities() {
            fetch(basePath + '/api-cities.php?province=Davao%20del%20Sur')
                .then(r => r.json())
                .then(data => {
                    document.getElementById('cities').textContent = JSON.stringify(data, null, 2);
                })
                .catch(e => {
                    document.getElementById('cities').textContent = 'Error: ' + e.message;
                });
        }
        
        function testBarangays() {
            fetch(basePath + '/api-barangays.php?province=Davao%20del%20Sur&city=Davao%20City')
                .then(r => r.json())
                .then(data => {
                    document.getElementById('barangays').textContent = JSON.stringify(data, null, 2);
                })
                .catch(e => {
                    document.getElementById('barangays').textContent = 'Error: ' + e.message;
                });
        }
    </script>
</body>
</html>
