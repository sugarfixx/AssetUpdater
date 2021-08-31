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
        const jsonString = httpGet('http://assetupdater.test/queue/run');
        const obj = JSON.parse(jsonString);
        if (obj)
            console.log(obj.lenght);
    </script>
</head>
<body>

</body>
</html>
