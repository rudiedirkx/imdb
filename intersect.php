<?php

require __DIR__ . '/inc.bootstrap.php';

include 'tpl.header.php';

?>
<style>
* { margin: 0; padding: 0; }
html {
	font-size: 100%;
}
body {
	padding: 20px;
}
p, ul, table, form {
	margin: 0 0 1em;
}
ul {
	padding-left: 2em;
}

/* search */
.list .empty:not(:last-child) {
	display: none;
}
#in {
	color: green;
}
#r {
	list-style: none;
	margin: 0;
	padding: 0;
}
#r:not(:empty) {
	border: solid 1px #000;
	background: #eee;
}
#r > li {
	padding: 3px;
	background: #ccc;
	line-height: 30px;
	font-size: 1.2rem;
}
#r > li:not(:first-child) {
	margin-top: 2px;
}
#r span {
	display: block;
}
#r .actions {
	line-height: 24px;
	font-size: 1rem;
}
#r img {
	float: left;
	width: 40px;
	height: 54px;
	border: 0;
	margin-right: 6px;
}
#r > li:after {
	content: "";
	display: block;
	clear: both;
	height: 0;
	visibility: hidden;
}

/* results */
table {
	border-collapse: collapse;
}
td, th {
	padding: 4px 7px;
	border: solid 1px #999;
}
.title, .actor {
	font-weight: bold;
}
</style>

<p><a href="find.php">Quicksearch here</a></p>

<?php

$titles = array();
if ( !empty($_GET['in']) ) {
	$titles = array_reduce($_GET['in'], function($list, $title) {
		$x = explode(' ', $title, 2);
		return $list + array($x[0] => $x[1]);
	}, array());

	$in = array_keys($titles);
	include 'search.php';
}

?>

<form action>

	<p>I'm looking for an actor/actress that's in these titles:</p>

	<ul class="list" id="in">
		<li class="empty">...</li>
		<? foreach ($titles as $id => $title): ?>
			<li data-id="<?= $id ?>" class="title-<?= $id ?>"><input type="hidden" name="in[]" value="<?= $id ?> <?= $title?>" /> <?= $title ?> (<a class="removeable" href="#">x</a>)</li>
		<? endforeach ?>
	</ul>

	<p><input type="submit" /></p>

</form>

<p>Find title: <input id="q" type="search" autocomplete="off" /></p>

<ul id="r"></ul>

<script>
/* mini framework */
(function() {
	window.$ = function(sel, ctx) {
		return (ctx || document).querySelector(sel);
	};
	window.$$ = function(sel, ctx) {
		return (ctx || document).querySelectorAll(sel);
	};
	var AP = Array.prototype;
	AP.invoke = function(method, args, rrv) {
		args || (args = []);
		var rv = [];
		this.forEach(function(el) {
			rv.push(el[method].apply(el, args));
		});
		return rrv ? rv : this;
	};
	var EP = Element.prototype;
	EP.on = function(type, sel, handler) {
		if (!handler) {
			handler = sel;
			sel = '';
		}
		this.addEventListener(type, sel ? function(e) {
			var el = e.target.is(sel) ? e.target : e.target.ancestor(sel);
			if (el) {
				handler.call(el, e);
			}
		} : handler);
		return this;
	};
	EP.is = EP.matches || EP.matchesSelector || EP.webkitMatchesSelector || EP.mozMatchesSelector || EP.msMatchesSelector || function(sel) {
		return $$(sel).indexOf(this) != -1;
	};
	EP.ancestor = function(sel) {
		var el = this;
		while ((el = el.parentNode) && el != document) {
			if (el.is(sel)) {
				return el;
			}
		}
	};
	HTMLCollection.prototype.on = NodeList.prototype.on = function(type, sel, handler) {
		return this.invoke('on', [type, sel, handler]);
	};
	['invoke', 'forEach', 'slice', 'filter', 'map', 'indexOf'].forEach(function(method) {
		HTMLCollection.prototype[method] = NodeList.prototype[method] = AP[method];
	});
})();



function addTitle(sr, list) {
	var id = sr.dataset.id,
		title = sr.ancestor('[data-title]').dataset.title;
	$('#' + list).innerHTML += '<li data-id="' + id + '" class="title-' + id + '"><input type="hidden" name="' + list + '[]" value="' + id + ' ' + title.replace(/"/g, '&quot;') + '" /> ' + title.replace(/</g, '&lt;') + ' (<a class="removeable" href="#">x</a>)</li>';
	$('#q').select();
}
function removeTitle(id, list) {
	$('#' + list + ' li.title-' + id).remove();
	$('#q').select();
}

function onlyTitles(results) {
	return results.filter(function(title) {
		return title.id && /^tt/.test(title.id);
	});
}

$('#in').on('click', 'a.removeable', function(e) {
	e.preventDefault();

	var li = this.ancestor('li'),
		id = li.dataset.id;
	removeTitle(id, li.parentNode.id);
});

$('#r').on('click', 'a[data-list]', function(e) {
	e.preventDefault();

	var list = this.dataset.list;
	addTitle(this, list);
});

$('#q').on('search', function(e) {
	var el = this,
		v = el.value.toLowerCase();
	if ( v != el.lastValue ) {
		el.lastValue = v;

		v = v.replace(/[\-\']/g, '').replace(/\W+/g, '_').replace(/_{2,}/g, '_').replace(/^_|_$/g, '');
		if ( v ) {
			var url = 'https://sg.media-imdb.com/suggests/' + v[0] + '/' + v + '.json';
			window['imdb$' + v] = function(rsp) {
				var titles = onlyTitles(rsp.d),
					html = '';
				titles.forEach(function(t) {
					html += '<li id="search-result-' + t.id + '" data-title="' + t.l + ' (' + t.y + ')"><span class="title">' + t.l + ' (' + t.y + ')</span> <span class="actions"><a data-list="in" data-id="' + t.id + '" href="#">Is in</a></span></li>';
				});
				$('#r').innerHTML = html;
			};
			var script = document.createElement('script');
			script.src = url;
			script.onerror = function(e) {
				// Try again, less specific
				el.value = el.value.slice(0, -1);
				el.dispatchEvent(new Event('search'));
			};
			document.head.appendChild(script);
		}
	}
})
</script>
