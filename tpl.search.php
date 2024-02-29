<form class="search" action="find.php">
	<p>
		<input name="q" value="<?= html($_GET['q'] ?? '') ?>" autocomplete="off" />
		<button>Search</button>
	</p>
</form>
