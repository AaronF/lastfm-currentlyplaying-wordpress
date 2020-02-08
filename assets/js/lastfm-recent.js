var lfm_recent = {
	nowPlaying: function(){
		return new Promise (function(resolve, reject) {
			jQuery.ajax({
				type : "post",
				dataType : "json",
				url : lastfm_recent.ajax_url,
				data : {
					action: "lastfm_currently_playing"
				},
				success: function(response) {
					resolve(response);
				}
			 });
		});
	}
}

window.addEventListener("load", function(e){
	function lfm_display_currently_playing(){
		var currently_playing_outputs = document.querySelectorAll(".lfm_currently_playing");
		if(currently_playing_outputs){
			lfm_recent.nowPlaying().then(function(result){
				for (let i = 0; i < currently_playing_outputs.length; i++) {
					const currently_playing_output = currently_playing_outputs[i];
					
					if(result.elapsed_time != null){
						currently_playing_output.innerHTML = result.name + " by " + result.artist + " - " + result.elapsed_time;
					} else {
						currently_playing_output.innerHTML = result.name + " by " + result.artist;
					}
				}
			}, function(error){
				console.log(error);
			});
		}
	}

	lfm_display_currently_playing();
	setInterval(() => {
		lfm_display_currently_playing();
	}, 90000);
});