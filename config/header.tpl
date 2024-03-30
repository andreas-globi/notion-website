<style>
    article { margin-top: 44px; min-height: 90vh; }
    body { white-space: normal; }
    .page-cover-image { }
	@media (max-width: 700px) {
        .page-cover-image { margin-left: -50vw; }
	}
	#globalheader { position: absolute; top: 0; left: 0.3vw; right: 0.3vw; background: #fff; white-space: normal; height: 44px; border-bottom: 1px solid #ddd; }
    #globalheader .globallogo { position: absolute; top: 2px; left: 0; cursor: pointer; }
    #globalheader .globallogo img { max-height: 40px; height: 40px; min-width: 40px; vertical-align: middle; }
    #globalheader .globallogo .globalbrand { display: inline-block; vertical-align: middle; margin-left: 0.3em; font-size: 22px; font-family: sans-serif; }
	#globalheader .globalsearchwrapper { position: absolute; top: 2px; right: 0; height: 40px; }
    #globalheader .globalsearchwrapper { display: inline-block; height: 40px; vertical-align: middle; text-align: right; }
    #globalheader .globalsearchwrapper .globalsearchfield { display: flex; align-items: center; height: 40px; }
    #globalheader .globalsearchwrapper .globalsearchfield div { display: inline-block; }
    #globalheader .globalsearchwrapper .globalsearchfield .globalsearchinput { position: relative; margin-right: 0.5em; }
    #globalheader .globalsearchwrapper .globalsearchfield .globalsearchinput #globalsearchinputfield { padding: 5px 10px; font-size: 15px; color: #333; border: 1px solid #ddd; }
    #globalheader .globalsearchwrapper .globalsearchfield .globalsearchinput #globalsearchinputfield:focus-visible { outline: 1px solid #bbb; }
    #globalheader .globalsearchwrapper .globalsearchfield .globalsearchicon { font-size: 18px; }
	#globalheader .globalsearchwrapper .globalsearchresults { position: absolute; z-index: 2; background: #fff; border: 1px solid #ddd; padding: 0.5em 1em; right: 27px; max-width: 50vw; width: max-content; text-align: left; max-height: 80vh; overflow-y: scroll; box-shadow: #888 2px 2px 5px; display: none; min-width: 18em; }
    #globalheader .globalsearchwrapper .globalsearchresults .searchresultsingle { border-bottom: 1px solid #eee; cursor: pointer; padding: 3px; }
    #globalheader .globalsearchwrapper .globalsearchresults .searchresultsingle:last-child { border-bottom: 0; }
    #globalheader .globalsearchwrapper .globalsearchresults .searchresultsingle .searchresultheader { margin: 3px 0; }
    #globalheader .globalsearchwrapper .globalsearchresults .searchresultsingle .searchresultheader span.icon { float: left; font-size: 1em; display: block; min-width: 1em; max-height: 1em; }
    #globalheader .globalsearchwrapper .globalsearchresults .searchresultsingle .searchresultinfo { font-size: 12px; color: #888; }
    #globalheader .globalsearchwrapper .globalsearchresults .searchresultsingle .searchresultinfo b { color: #666; background: #ffc; }
    #globalheader .globalsearchwrapper .globalsearchresults .searchresultsingle:hover { background: #eee; }
    @media (max-width: 1100px) {
        #globalheader .globalsearchwrapper .globalsearchresults { max-width: 60vw; }
    }
    @media (max-width: 900px) {
        #globalheader { left: 0.2vw; right: 0.2vw; }
        #globalheader .globalsearchwrapper .globalsearchresults { max-width: 70vw; }
    }
    @media (max-width: 700px) {
        #globalheader .globalsearchwrapper .globalsearchresults { max-width: 80vw; }
    }
    @media (max-width: 500px) {
        #globalheader { left: 0.1vw; right: 0.1vw; }
        #globalheader .globalsearchwrapper .globalsearchfield .globalsearchinput #globalsearchinputfield { width: 6em; }
    }
    @media (max-width: 300px) {
        #globalheader .globalsearchwrapper .globalsearchfield .globalsearchinput #globalsearchinputfield { width: 4em; }
    }
</style><div id="globalheader">
	<div class="globallogo">
		<img src="{{logoimage}}" alt="{{brandname}}">
		<div class="globalbrand">{{brandname}}</div>
	</div>
	<div class="globalsearchwrapper">
		<div class="globalsearchfield">
			<div class="globalsearchinput">
				<input type="text" id="globalsearchinputfield" autocomplete="off">
			</div>
			<div class="globalsearchicon"><svg viewBox="0 0 17 17" class="searchNew" style="width: 14px; height: 14px; display: block; fill: inherit; flex-shrink: 0; backface-visibility: hidden; margin-right: 6px;"><path d="M6.78027 13.6729C8.24805 13.6729 9.60156 13.1982 10.709 12.4072L14.875 16.5732C15.0684 16.7666 15.3232 16.8633 15.5957 16.8633C16.167 16.8633 16.5713 16.4238 16.5713 15.8613C16.5713 15.5977 16.4834 15.3516 16.29 15.1582L12.1504 11.0098C13.0205 9.86719 13.5391 8.45215 13.5391 6.91406C13.5391 3.19629 10.498 0.155273 6.78027 0.155273C3.0625 0.155273 0.0214844 3.19629 0.0214844 6.91406C0.0214844 10.6318 3.0625 13.6729 6.78027 13.6729ZM6.78027 12.2139C3.87988 12.2139 1.48047 9.81445 1.48047 6.91406C1.48047 4.01367 3.87988 1.61426 6.78027 1.61426C9.68066 1.61426 12.0801 4.01367 12.0801 6.91406C12.0801 9.81445 9.68066 12.2139 6.78027 12.2139Z"></path></svg></div>
		</div>
		<div class="globalsearchresults sans">
		</div>
	</div>
</div>
<script>
	// global vars for common.js
	// note these will get replaced out in index.php by the actual config
	window.globalvars = {
		"brandname": "{{brandname}}",
		"logoimage": "{{logoimage}}",
		"pathfromroot": "{{pathfromroot}}",
		"enablesearch": {{enablesearch}}
	};
</script>
