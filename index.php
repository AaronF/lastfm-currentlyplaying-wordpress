<html>
	<head>
		<title>Test page</title>
	</head>
	<body>
		<div id="currently_playing"></div>

		<script type="text/javascript">
			var lastfm = {
				nowPlaying: function(){
					return new Promise (function(resolve, reject) {
						var xhr = new XMLHttpRequest();
						xhr.onload = function () {
							if (xhr.readyState === xhr.DONE && xhr.status === 200) {
								var response = JSON.parse(xhr.responseText);
								resolve(response);
							} else {
								reject(response);
							}
						}
						xhr.open('GET', '/fetch-playing.php', true);
						xhr.setRequestHeader("Content-Type", "application/json;charset=UTF-8");
						xhr.send();
					});
				}
			}
			window.addEventListener("load", function(e){
				function displayCurrentlyPlaying(){
					var currently_playing = document.getElementById("currently_playing");
					if(currently_playing){
						lastfm.nowPlaying().then(function(result){
							if(result.elapsed_time != null){
								currently_playing.innerHTML = result.name + " by " + result.artist + " - " + result.elapsed_time;
							} else {
								currently_playing.innerHTML = result.name + " by " + result.artist;
							}
						}, function(error){
							console.log(error);
						});
					}
				}

				displayCurrentlyPlaying();
				setInterval(() => {
					console.log("Refresh!");
					displayCurrentlyPlaying();
				}, 90000);
			});
		</script>
	</body>
</html>