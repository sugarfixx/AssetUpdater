<!DOCTYPE html>
<html>
<head>
    <title>Poll</title>
    <script type="text/javascript">
        function httpGet(theUrl) {
            var xmlHttp = new XMLHttpRequest();
            xmlHttp.open( "GET", theUrl, false ); // false for synchronous request
            xmlHttp.send( null );
            return xmlHttp.responseText;
        }

        window.setInterval(function () {
            console.log('Starting fn240');
            const jsonString = httpGet('http://assetupdater.test/queue/run');
            const obj = JSON.parse(jsonString);
            console.log(obj.length)
        },240000);

    </script>
</head>
<body>

</body>
</html>
