<style>
input.searching {
	background-color: #ddd;
}
</style>

<form class="search" action="find.php">
	<p>
		<input name="q" value="<?= html($_GET['q'] ?? '') ?>" autocomplete="off" />
		<button>Search</button>
	</p>
	<ul id="results"></ul>
</form>

<script>
(function() {
	const cache = {};
	const el = document.querySelector('input[name="q"]');
	let searching = null;
	el.addEventListener('input', function(e) {
		const v = this.value.replace(/[\-\']/g, '').replace(/\W+/g, '_').replace(/_{2,}/g, '_').replace(/^_|_$/g, '');
		if (v.length < 3) return;

		const url = 'https://sg.media-imdb.com/titles/' + v[0] + '/' + v + '.json';
		searching = v;
		sessionStorage.imdbSearching = this.value;
		window['imdb$' + v] = (v => function(rsp) {
			cache[v] = rsp;

			if (v !== searching) return console.warn(`discarding result of '${v}' because searching for '${searching}'`);
			el.classList.remove('searching');

			const titles = rsp.d; // .filter(item => item.id.startsWith('tt'));
			let html = '';
			titles.forEach(function(t) {
				html += '<li><a href="title.php?id=' + t.id + '">' + t.l + '</a> (' + t.y + ', ' + t.s + ')</li>';
			});
			document.querySelector('#results').innerHTML = html;

			window.scrollTo(0, 9999);
		})(v);

		if (cache[v]) {
			window['imdb$' + v](cache[v]);
			return;
		}

		el.classList.add('searching');
		const script = document.createElement('script');
		script.src = url;
		script.onerror = function(e) {
			// Try again, less specific
			el.value = el.value.slice(0, -1);
			el.dispatchEvent(new Event('input'));
		};
		document.head.appendChild(script);
	});

	el.addEventListener('focus', function(e) {
		if (el.value) {
			el.dispatchEvent(new Event('input'));
		}
	});

	if (sessionStorage.imdbSearching) {
		el.value = sessionStorage.imdbSearching;
	}
})();
</script>
