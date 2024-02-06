<form class="search" action>
	<p>
		<input name="q" value="<?= html($_GET['q'] ?? '') ?>" autocomplete="off" />
		<button>Search</button>
	</p>
</form>
