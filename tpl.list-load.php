<style>
li.last-before-load {
	background-color: yellow;
}
</style>

<script>
window.onload = function() {
	const ul = document.querySelector('ul');
	const showing = document.querySelector('#showing-num');

	let cursor = '<?= $pager->cursor ?>';
	let loading = false;
	window.onscroll = async function(e) {
		const atBottom = document.documentElement.scrollTop + window.innerHeight > document.documentElement.offsetHeight - 20;
		if (!atBottom || loading || !cursor) return;

		ul.lastElementChild.classList.add('last-before-load');

		loading = true;
		const rsp = await fetch('?cursor=' + cursor);
		cursor = rsp.headers.get('imdb-cursor');
		const html = await rsp.text();
		ul.innerHTML += html;
		showing.textContent = ul.childElementCount;

		loading = false;
	};
};
</script>
