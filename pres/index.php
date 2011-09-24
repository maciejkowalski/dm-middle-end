<?

function f($c) {
	return htmlspecialchars(
		preg_replace('/(^\n|\n$)/', '',
			str_replace("\t", '    ', $c)
		)
	);
}

?><!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>Prezentacja - DevMeetings - middle-end w NodeJS</title>
	<meta name="viewport" content="width=1024, user-scalable=no">
	<link rel="stylesheet" href="deck.core.css">
	<link rel="stylesheet" href="themes/style/swiss.css">
	<link rel="stylesheet" href="extensions/navigation/deck.navigation.css">
	<link rel="stylesheet" href="extensions/status/deck.status.css">
	<link rel="stylesheet" href="extensions/hash/deck.hash.css">
	<link rel="stylesheet" href="sh/styles/shCore.css">
	<link rel="stylesheet" href="sh/styles/shThemeDefault.css">
	<link rel="stylesheet" href="themes/my_style.css">
</head>
<body class="deck-container">

	<header class="slide" id="intro">
		<h1>Middle-end w oparciu o NodeJS</h1>
		<p class="author">by DevMeetings (Piotrek Koszuliński; @reinmarpl; http://code42.pl)</p>
	</header>

	<section class="slide">
		<h1>Wielki nieobecny</h1>
		<img src="imgs/IMG_2777.jpg" alt="D">
	</section>

	<section class="slide">
		<h1>Wielki nieobecny</h1>
		<img src="imgs/IMG_2777_d.jpg" alt="D">
	</section>

	<section class="slide">
		<h1>Organizatorzy</h1>
		<img src="imgs/IMG_2777_o.jpg" alt="O">
	</section>

	<section class="slide">
		<h1>CSJS vs SSJS</h1>
	</section>

	<section class="slide">
		<h2>Client-side JavaScript</h2>
		<ul>
			<li>synchroniczne API</li>
			<li>
<pre><?= f('
<script src="jquery-1.6.4.min.js"></script>
<script src="modernizr.custom.js"></script>
<script src="deck.core.js"></script>
<script>
	$(function() {
		$.deck(\'.slide\');
	});
</script>
') ?></pre>
			</li>
			<li>BOM i DOM</li>
			<li><code>window === this</code></li>
			<li><code>this.test = 1; test; //-&gt; 1</code></li>
		</ul>
	</section>

	<section class="slide">
		<h2>Server-side JavaScript (w NodeJS)</h2>
		<ul>
			<li><strong>a</strong>synchroniczne API</li>
			<li>
<pre><?= f("
var url = require('url'),
	utils = require('./utils'),
	EventEmitter = utils.EventEmitter,
	Model = require('./model');
") ?></pre>
			</li>
			<li>czysty global</li>
			<li><code>global !== this</code></li>
			<li><code>this.test = 1; test; // -&gt; Reference error</code> (uwaga na testowanie w konsoli Node'a)</li>
		</ul>
	</section>

	<section class="slide">
		<h2>Asynchroniczna pułapka</h2>
		<h3>Złap errora &ndash; <code>def</code> czy <code>call</code>?</h3>
<pre><?= f("
try {
	var fn = function () {
		throw new Error();
	};
}
catch (e) {
	console.log('def', e);
}

try {
	setTimeout(fn, 1);
}
catch (e) {
	console.log('call', e);
}
") ?></pre>
	</section>

	<section class="slide">
		<h2>Asynchroniczna pułapka</h2>
		<h3>Złap errora &ndash; <code>def</code> czy <code>call</code>?</h3>

		<p><img src="imgs/IMG_2559.jpg" alt="Facepalm by David" width="600"></p>

		<ul>
			<li class="slide">Nie <code>def</code>, nie <code>call</code>, wyjątek leci w kosmos</li>
			<li class="slide">Node'owa konwencja: <code>fs.writeFile('sth', function (err, result) {});</code></li>
		</ul>
	</section>

	<section class="slide">
		<h2>Asynchroniczna pułapka</h2>
		<h3>Złap errora &ndash; nie tak, to jak?</h3>
		
		<div class="slide">
<pre><?= f("
process.on('uncaughtException', function (err) {
	console.log('ups', err);
});
") ?></pre>
		</div>
	</section>

	<section class="slide">
		<h2>Asynchroniczna pułapka</h2>
		<h3>Typowy przypadek</h3>
<pre><?= f("
app.get('/post/:title', function (req, res) {
	db.getPostByTitle(req.params.title, function (err, post) {
		if (err) throw err;
		db.getComments(post.id, function (err, comments) {
			if (err) throw err;
			res.render({
				post: post,
				comments: comments
			});
		});
	});
});
") ?></pre>
		<ul>
			<li class="slide">A co jeśli będziemy chcieli pobrać jeszcze listę tagów?</li>
		</ul>
	</section>

	<section class="slide">
		<h2>Asynchroniczna pułapka cz.2.</h2>
		<h3>Konkatenacja plików w katalogu</h3>
		<ol>
			<li>odczytanie listy plików w katalogu</li>
			<li>odczytanie zawartości każdego z plików</li>
			<li><strong>synchronizacja!</strong></li>
			<li>połączenie zawartości</li>
			<li>zapisanie pliku wynikowego</li>
		</ol>
	</section>

	<section class="slide">
		<h2>Asynchroniczna pułapka cz.2.</h2>
		<h3>Konkatenacja plików w katalogu</h3>
		
<pre><?= f("
fs.readdir(__dirname, function (err, names) {
	var l = names.length, opened = 0, content = [];
	names.foreach(function (name, i) {
		fs.readfile(name, 'utf-8', function (err, c) {
			content[i] = c;
			if (++opened === l) write();
		});
	});
	var write = function () {
		fs.writefile('lib.js', content.join(\"\\n\"), function (err) {
			console.log('done');
		});
	};
});
") ?></pre>
		
		<ul>
			<li class="slide">A obsługa błędów?</li>
			<li class="slide">A można łatwiej?</li>
		</ul>
	</section>

	<section class="slide">
		<h2>A można łatwiej?</h2>
		<h3>A zgadnij</h3>
		<ul class="slide">
			<li>https://github.com/caolan/async</li>
			<li>https://github.com/medikoo/deferred</li>
		</ul>
		<p class="slide">Więcej:</p>
		<ul class="slide">
			<li>https://github.com/kriskowal/q</li>
			<li>https://github.com/creationix/step</li>
			<li>https://github.com/willconant/flow-js</li>
			<li>https://github.com/fjakobs/async.js</li>
			<li>https://github.com/kriszyp/node-promise</li>
			<li>https://github.com/laverdet/node-fibers</li>
			<li>... itd., itd.</li>
		</ul>
	</section>

	<section class="slide">
		<h2>Async</h2>
		<ul>
			<li><code>series(tasks, [callback])</code> &ndash; Run an array of functions in series, each one running once the previous function has completed.</li>
			<li><code>parallel(tasks, [callback])</code> &ndash; Run an array of functions in parallel, without waiting until the previous function has completed.</li>
			<li><code>waterfall(tasks, [callback])</code> &ndash; Runs an array of functions in series, each passing their results to the next in the array.</li>
			<li>i wiele innych</li>
		</ul>
	</section>

	<section class="slide">
		<h2>Async</h2>
		<h3>Typowy przypadek</h3>
<pre><?= f("
app.get('/post/:title', function (req, res) {
	async.waterfall([
		function (callback) {
			db.getPostByTitle(req.params.title, callback);
		},
		function (post, callback) {
			db.getComments(post.id, function (comments) {
				callback(null, post, comments);
			});
		}
	],
	function (err, post, comments) {
		if (err) throw err;
		res.render({
			post: post,
			comments: comments
		});
	}
});
") ?></pre>
	</section>

	<section class="slide">
		<h2>Async</h2>
		<h3>Typowy przypadek</h3>
		<ul>
			<li class="slide">kod się wydłużył...</li>
			<li class="slide">... ale łatwiej się go czyta</li>
			<li class="slide">... i łatwiej rozwija</li>
			<li class="slide">ale czy da się jeszcze łatwiej?</li>
		</ul>
	</section>

	<section class="slide">
		<h2>Deferred i promise'y</h2>

		<p>Załóżmy, że mamy asynchroniczną funkcję:</p>

<pre><?= f("
var oneOneSecondLater = function (callback) {
    setTimeout(function () {
        callback(1);
    }, 1000);
};

oneOneSecondLater(function (v) { console.log(v); });
") ?></pre>
		
		<ul class="slide">
			<li>musimy zagnieżdżać wywołania</li>
			<li>nie możemy przekazać "stanu odroczenia" jako wartości</li>
		</ul>
	</section>

	<section class="slide">
		<h2>Deferred i promise'y</h2>

		<p>Spróbujmy inaczej:</p>

<pre><?= f("
var maybeOneOneSecondLater = function () {
    var callback;
    setTimeout(function () {
        callback(1);
    }, 1000);
    return {
        then: function (_callback) {
            callback = _callback;
        }
    };
};

maybeOneOneSecondLater().then(function (v) {
	console.log(v);
});
") ?></pre>

		<ul>
			<li class="slide">lepiej!</li>
			<li class="slide">i to dopiero początek</li>
		</ul>
	</section>

	<section class="slide">
		<h2>Deferred i promise'y</h2>

		<p>Po drobnej reaktoryzacji:</p>

<pre><?= f("
var defer = function () {
	var callback = null, _value;
    return {
        resolve: function (_value) {
            value = _value;
            callback(value);
            callback = undefined;
        },
        then: function (_callback) {
			if (callback === null)
				callback = _callback;
			else
				_callback(value);
        }
    }
};

var oneOneSecondLater = function () {
    var result = defer();
    setTimeout(function () {
        result.resolve(1);
    }, 1000);
    return result;
};

oneOneSecondLater().then(callback);
") ?></pre>

		<ul>
			<li class="slide">to wciąż dopiero początek</li>
			<li class="slide">całość rozmyślań u Krisa Kowala: https://github.com/kriskowal/q/blob/master/design/README.js</li>
		</ul>

	</section>

	<section class="slide">
		<h2>Deferred i promise'y</h2>
		<h3>Deferred by Medikoo</h3>
		<ul>
			<li>https://github.com/medikoo/deferred</li>
			<li class="slide">made in Poland</li>
			<li class="slide">zainspirowany przez implementację Krisa Kowala i innych</li>
			<li class="slide">ciekawie współpracuje z es5-ext (https://github.com/medikoo/es5-ext)</li>
			<li class="slide">kod wykorzystujący deferred i es5-ext przestaje przypominać JavaScript (to plus, czy minus?)</li>
			<li class="slide">
<pre><?= f("
a2p(fs.readFile, __filename, 'utf-8')
(invoke('toUpperCase'))
(console.log)
.end();
") ?></pre>
			</li>
			<li class="slide">:|</li>
		</ul>

	</section>

	<section class="slide">
		<h2>Deferred i promise'y</h2>
		<h3>Deferred by Medikoo</h3>

<pre><?= f("
var later = function () {
  var d = deferred();
    setTimeout(function () {
        d.resolve(1);
    }, 1000);
    return d.promise;
};

later().then(function (n) {
    console.log(n); // 1
});
") ?></pre>

	<p class="slide">Ale <code>promise</code>, to tak naprawdę <code>then</code>, więc prościej:</p>

<div class="slide"><pre><?= f("
later()
(function (n) {
    console.log(n); // 1
});
") ?></pre></div>

	</section>

	<section class="slide">
		<h2>Deferred i promise'y</h2>
		<h3>Deferred by Medikoo</h3>

		<ul>
			<li class="slide">waterfall:
<pre><?= f("
later()
(function (n) {
	return n + 1;
})
(function (n) {
	console.log(n); // 2
});
") ?></pre>
			</li>
			<li class="slide">async to promise:
<pre><?= f("
var a2p = deferred.asyncToPromise.call,
	ba2p = deferred.asyncToPromise.bind;

a2p(fs.readFile, __filename, 'utf-8')
(function (content) {
	// change content
	return content;
})
(ba2p(fs.writeFile, __filename + '.changed'));
") ?></pre>
			</li>
		</ul>
	</section>

	<section class="slide">
		<h2>Deferred i promise'y</h2>
		<h3>Deferred by Medikoo</h3>

		<ul>
			<li class="slide"><code>join(...)</code>:
<pre><?= f("
join(p1, p2, p3)
(function (result) {
    // result is array of resolved values of p1, p2 and p3.
});
") ?></pre>
			</li>
			<li class="slide">obsługa błędów:
<pre><?= f("
later()(function (n) { throw new Error('error!'); })
(function () {
    // never called
}, function (e) {
    // handle error;
});
") ?></pre>
			</li>
			<li class="slide">bądź:
<pre><?= f("
later()(function (n) { throw new Error('error!'); })
.end(function (e) {
    // handle error!
});
") ?></pre>
			</li>
		</ul>
	</section>

	<section class="slide">
		<h2>Deferred i promise'y</h2>
		<h3>Deferred by Medikoo</h3>
		<p>Konkatenacja plików w katalogu:</p>

<pre><?= f("
all(
    // Read all filenames in given path
    a2p(fs.readdir, __dirname),

    // Read files content
    function (files) {
        return join(files.map(function (name) {
            return a2p(fs.readFile, name, 'utf-8');
        }));
    },

    // Concat into one string
    function (data) {
        return data.join(\"\\n\");
    },

    // Write to lib.js
    ba2p(fs.writeFile, __dirname + '/lib.js')
).end();
") ?></pre>

	</section>

	<section class="slide">
		<h2>Deferred i promise'y</h2>
		<h3>Deferred by Medikoo</h3>
		<p>W skrócie:</p>

<pre><?= f("
all(
    a2p(fs.readdir, __dirname),

    invoke('map', function (name) {
        return a2p(fs.readFile, name, 'utf-8');
    }), join,

    invoke('join', \"\\n\"),

    ba2p(fs.writeFile, __dirname + '/lib.js')
).end();
") ?></pre>

	</section>

	<section class="slide">
		<h2>Deferred i promise'y</h2>
		<h3>Deferred by Medikoo</h3>
		<p>Nasz typowy przypadek:</p>

<pre><?= f("
all(
	a2p(db.getPostByTitle.bind(db), 't'),
	function (post) {
		return a2p(db.getComments.bind(db), post.id);
	}
)
(function (args) {
	res.render({
		post: args[0],
		comments: args[1]
	});
})
.end(function (err) {
	//sth
});
") ?></pre>

	</section>

	<section class="slide">
		<h2>Deferred i promise'y</h2>
		<h3>Deferred by Medikoo</h3>
		<p>Bądź gdybyśmy korzystali z deferred również w bazie:</p>

<pre><?= f("
all(
	db.getPostByTitle('t'),
	function (post) {
		return db.getComments(post.id);
	}
)
(function (args) {
	res.render({
		post: args[0],
		comments: args[1]
	});
})
.end(function (err) {
	//sth
});
") ?></pre>

	</section>

	<section class="slide">
		<h2>Async czy deferred?</h2>
		<ul>
			<li class="slide">Async
				<ul>
					<li>łatwiej pojąć</li>
					<li>pasuje w każdym przypadku</li>
				</ul>
			</li>
			<li class="slide">deferred
				<ul>
					<li>ma większe możliwości (stan jako wartość)</li>
					<li>lepiej działa jeśli jest szeroko wykorzystywany</li>
					<li>trzeba się do niego przyzwyczaić</li>
					<li>duży wybór implementacji</li>
				</ul>
			</li>
		</ul>
	</section>

	<section class="slide">
		<h2>Asynchroniczna pułapka cz.3.</h2>
		<h3>Jak debugować wywołania funkcji anonimowych?</h3>
		<ul>
			<li class="slide">gdzie wystąpił błąd?
<pre><?= f("
> (function () { throw new Error('Ratunku'); }());
Error: Ratunku
    at repl:1:22
    at repl:1:44
    at REPLServer.eval (repl.js:80:28)
    at repl.js:178:16
") ?></pre>
			</li>
			<li class="slide">łatwiej z NFE (named function expression):
<pre><?= f("
> (function fn() { throw new Error('Ratunku'); }());
Error: Ratunku
    at fn (repl:1:24)
    at repl:1:46
    at REPLServer.eval (repl.js:80:28)
    at repl.js:178:16
") ?></pre>
			</li>
			<li class="slide">nie mylić NFE z FD (function declaration): http://code42.pl/2011/08/20/co-lepiej-wiedziec-o-javascriptcie-cz-2-hoisting-deklaracje-funkcji-i-wyrazenia-funkcyjne/</li>
		</ul>
	</section>

	<section class="slide">
		<h2>Moduły w NodeJS</h2>
		<ul>
			<li class="slide">implementacja Modules/1.0 z CommonJS &ndash; http://wiki.commonjs.org/wiki/Modules/1.0</li>
			<li class="slide">przydatny niestandardowy dodatek: <code>modules.exports</code></li>
			<li class="slide">św. Graal programistów JS</li>
				<ul>
					<li class="slide">zabezpieczenie globalnego scope'a (<code>global !== this</code> i osobne scope'y)</li>
					<li class="slide">łatwy import potrzebnych modułów</li>
					<li class="slide">hermetyzacja i czytelne publiczne API modułów</li>
					<li class="slide">jednolity standard &ndash; od dzisiaj każdy pisze tak samo</li>
					<li class="slide">moduły + metainformacje (package.json) =&gt; pakiet (również ustandaryzowane w CommonJS &ndash; http://wiki.commonjs.org/wiki/Packages/1.0)</li>
					<li class="slide">synchroniczna funkcja <code>require()</code>...</li>
					<li class="slide">... brak sensownej implementacji dla przeglądarek <strong>:(</strong></li>
				</ul>
			</li>
		</ul>
	</section>

	<section class="slide">
		<h2>Moduły w NodeJS</h2>
		<h3>Przykład</h3>
		
		<p>math.js:</p>
<pre><?= f("
exports.add = function () {
    var sum = 0, i = 0, args = arguments, l = args.length;
    while (i < l) {
        sum += args[i++];
    }
    return sum;
};
") ?></pre>
		
		<p>add.js:</p>
<pre><?= f("
var add = require('./math').add;
exports.increment = function (val) {
    return add(val, 1);
};
") ?></pre>

		<p>program.js:</p>
<pre><?= f("
var inc = require('./increment').increment;
var a = 1;
inc(a); // 2
") ?></pre>
	
	</section>
	
	<section class="slide">
		<h2>Moduły w NodeJS</h2>
		
		<ul>
			<li>jeśli ścieżka nie zaczyna się od <code>'/'</code> lub <code>'./'</code>, wtedy moduł poszukiwany jest w:
				<ul>
					<li>Node 0.4.x: <code>module.paths</code> + <code>require.paths</code></li>
					<li>Node 0.5.x: <code>module.paths</code> + <code>NODE_PATH</code> (łączone z: <code>$HOME/.node_modules, $HOME/.node_libraries, $PREFIX/lib/node</code>)</li>
					<li>
<pre><?= f("
#:/www/dmme$ node
> module.paths
[ '/www/dmme/repl/node_modules',
  '/www/dmme/node_modules',
  '/www/node_modules',
  '/node_modules' ]
") ?></pre>
					</li>
				</ul>
			</li>
			<li class="slide">globalna instalacja modułów w npm:
				<ol>
					<li><code>sudo npm install -g async</code></li>
					<li>moduł ląduje w: <code>/usr/local/lib/node_modules/</code></li>
					<li><code>cd ~ && ln -s /usr/local/lib/node_modules .node_modules</code></li>
					<li>tadam! instalujemy moduły raz a dobrze</li>
				</ol>
			</li>
		</ul>
	</section>

	<section class="slide">
		<h2>Moduły w NodeJS</h2>
		<h3>Jak działają?</h3>

<pre><?= f("
var exports = {}, module = { exports: exports };
function (exports, module, require) {
	// ciało modułu
}.call(exports, module, require);

exports; // publiczne API modułu
") ?></pre>
	
		<ul class="slide">
			<li>i wszystko jasne</li>
			<li>a więc sportujmy moduły do CSJS! opakujemy pliki jak wyżej i gotowe</li>
			<li>dwie przeszkody:</li>
				<ul>
					<li>synchroniczny <code>require</code> &ndash; odpada dynamiczne ładowanie skryptów</li>
					<li>konkatenacja skryptów i tak jest nieunikniona &ndash; ziarnistość kodu modułowego</li>
				</ul>
			</li>
		</ul>
	</section>

	<section class="slide">
		<h2>Przenośne moduły</h2>
		<h3>modules-webmake</h3>
		<ul>
			<li>https://github.com/medikoo/modules-webmake</li>
			<li class="slide">znowu Mariusz Nowak i znowu made in Poland</li>
			<li class="slide">pakujemy: <code>webmake app.js app_webmade.js</code></li>
			<li class="slide">webmake automatycznie prześledzi wszystkie wywołania <code>require()</code></li>
			<li class="slide">rezultat: samowykonujący się kod <code>app.js</code> z wszystkimi zależnościami</li>
			<li class="slide">minusy:
				<ul>
					<li>statyczna analiza wywołań <code>require()</code></li>
					<li>na dzień dzisiejszy konkatenacja do jednego pliku wyjściowego</li>
					<li>pracujemy nad tym</li>
				</ul>
			</li>
		</ul>
	</section>

	<section class="slide">
		<h2>Przenośne moduły</h2>
		<h3>RequireJS</h3>
		<ul>
			<li>http://requirejs.org/</li>
			<li class="slide">implementuje AMD (asynchronous model definition) &ndash; https://github.com/amdjs/amdjs-api/wiki/AMD</li>
			<li class="slide">zmienia sposób definicji modułów:
<pre><?= f("
define('alpha', ['require', 'exports', 'beta'],
	function (require, exports, beta) {
		exports.verb = function() {
			return beta.verb();
			// Or:
			return require('beta').verb();
		}
});
") ?></pre>
			</li>
			<li class="slide">posiada wbudowany konwerter CommonJS =&gt; AMD &ndash; https://github.com/jrburke/r.js</li>
			<li class="slide">nie udało mi się zmusić go do współpracy z typowymi pakietami Node'owymi</li>
			<li class="slide">warto się przyjrzeć, bo AMD staje się popularny</li>
		</ul>
	</section>

	<section class="slide">
		<h2>Przenośne moduły</h2>
		<h3>Podsumowanie</h3>
		<ul>
			<li>nie ma jednego, skutecznego rozwiązania</li>
			<li class="slide">sprawdzone przeze mnie rozwiązania są ubogie i toporne</li>
			<li class="slide">implementacja CommonJS w CSJS nie jest trywialna</li>
			<li class="slide">trwają prace nad AMD i modułami natywnymi (w ECMAScript Harmony &ndash; http://wiki.ecmascript.org/doku.php?id=harmony:modules)</li>
			<li class="slide">żadne ze specyfikacji nie wydają się być ze sobą zgodne</li>
			<li class="slide">składnia w Harmony: <code>module math { export x }</code></li>
			<li class="slide">jak napisać polyfilla?</li>
			<li class="slide">jeszcze długo przyjdzie nam poczekać...</li>
			<li class="slide">bonus: https://github.com/jbrantly/yabble i https://github.com/codespeaks/modulr</li>
		</ul>
	</section>

	<section class="slide">
		<h2>CSJS @ SSJS</h2>
		<ul>
			<li>załóżmy, że mamy kod CSJS</li>
			<li>chcemy go wykonać na serwerze</li>
			<li>kod ten korzysta z DOM-a i BOM-a jako globali</li>
			<li>sam też tworzy dużo globali</li>
			<li>chcemy go uruchomić w Nodzie</li>
			<li class="slide">JSDOM &ndash; serwerowa implementacja DOM-a i BOM-a (https://github.com/tmpvar/jsdom)</li>
			<li class="slide">node'owy moduł VM &ndash; piaskownica dla skryptów (http://nodejs.org/docs/v0.5.7/api/vm.html#vm.runInContext)</li>
			<li class="slide">uwaga: pełna dokumentacja jest dostępna tylko dla wersji 0.5.x, ale działa również z 0.4.x</li>
			<li class="slide">Contextify &ndash; wygodniejsza od VM implementacja sandboksa (https://github.com/brianmcd/contextify)</li>
		</ul>
	</section>

	<section class="slide">
		<h2>CSJS @ SSJS</h2>

<pre><?= f("
var jsdom = require('jsdom'),
	doc = jsdom.jsdom('<html><body></body></html>'),
	window = doc.createWindow();
window.console = console;
window.run(require('fs').readFileSync('jquery.js', 'utf-8'));
window.run('$(\"body\").append(\"<p>Kopytko!</p>\");');
window.run('console.log(document.innerHTML);');
// -> <html><body><p>Kopytko!</p></body></html>
") ?></pre>
	
		<div class="slide"><p>Alternatywnie:</p>

<pre><?= f("
jsdom.env({
	html: '<html><body></body></html>',
	src: [
		require('fs').readFileSync('jquery.js', 'utf-8'),
		'$(\"body\").append(\"<p>Kopytko!</p>\");'
	],
	done: function (err, window) { console.log(window.document.innerHTML); }
});
// -> <html><body><p>Kopytko!</p></body></html>
// -> <html><body><p>Kopytko!</p></body></html> (WTF?)
") ?></pre>
		</div>

	</section>

	<section class="slide">
		<h1>Middle-end</h1>
	</section>

	<section class="slide">
		<h2>Middle-end</h2>
		<blockquote>
			<p>What sits between the front-end of a web application and the back-end of an application? The “middle-end”, naturally!</p>
			<p><cite>Kyle Simpson @getify<br>http://blog.getify.com/2010/07/how-to-begin-your-middle-end/</cite></p>
		</blockquote>
		<ul>
			<li class="slide">idea promowana przez Kyle'a Simpsona</li>
			<li class="slide">warstwa pośrednicząca pomiędzy backendem i frontendem</li>
			<li class="slide">skupiająca wspólne zadania i dwukierunkową komunikację</li>
			<li class="slide">ktoś coś rozumie? bo ja nie...</li>
		</ul>
	</section>

	<section class="slide">
		<h2>Middle-end</h2>
		<h3>Zostawmy Kyle'a w spokoju</h3>
		<ul>
			<li class="slide">jak rozumiem middle-end?</li>
			<ul>
				<li class="slide">kierowanie się zasadą DRY przez wydzielenie wspólnego mianownika z backendu i frontendu</li>
				<li class="slide">wydzielenie warstwy równo związanej z backendem jak i frontendem</li>
			</ul>
			<li class="slide">zastosowania:
				<ul>
					<li>wspólne systemy szablonów</li>
					<li>wspólny routing</li>
					<li>wspólna walidacja</li>
					<li>formatowanie danych</li>
					<li>pakowanie (konkatenacja, minifikacja)</li>
					<li>cache (po stronie klienta, bądź serwera)</li>
					<li>...</li>
				</ul>
			</li>
		</ul>
	</section>

	<section class="slide">
		<h2>Zadania</h2>
		<h3>System template'owy</h3>
		<ul>
			<li>przenośny i dynamiczny (po stronie klienta) system template'owy</li>
			<li>JSON na wejściu, HTML na wyjściu</li>
			<li>na serwerze renderujemy od zera, na kliencie wpasowujemy się w podstawowy szablon</li>
			<li>zmiana danych =&gt; uktualizujemy tylko elementy zależne (widgety? eventy na JSON-owych danych?)</li>
			<li>moja propozycja:</li>
		</ul>
	</section>

	<section class="slide">
		<h2>Zadania</h2>
		<h3>Routing</h3>
		<ul>
			<li>przenośny system kontrolerów</li>
			<li>API do definicji akcji (np. à la Express <code>app.get, app.post</code>)</li>
			<li>na kliencie adapter oparty o <code>pushState()</code></li>
			<li>na serwerze adapter wpięty np. do Connecta jako middleware</li>
			<li>na kliencie przechwytywanie odpowiednich eventów</li>
			<li>parsowanie URL-i, obiekty <code>request, response</code></li>
			<li>moja propozycja:</li>
		</ul>
	</section>

	<section class="slide">
		<h2>Zadania</h2>
		<h3>Proxy v1</h3>
		<ul>
			<li>przezroczyste dla klienta i serwera</li>
			<li>operujące na kodzie HTML wyrenderowanej strony</li>
			<li>wykonujące jedną/kilka z czynności:
				<ul>
					<li>zamiana wszystkich linków na skrócone z bit.ly</li>
					<li>zamiana wszystkich numerów telefonów na widgety Skype'a</li>
					<li>dodawanie do wszystkich adresów linków do Google Mapsów</li>
					<li>i18n &ndash; wychodzący kod HTML zawiera specjalne znaczniki, podmieniane przez proxy według słowników (Google Translate, bądź statyczne-prywatne)</li>
				</ul>
			</li>
		</ul>
	</section>

	<section class="slide">
		<h2>Zadania</h2>
		<h3>Proxy v2</h3>
		<ul>
			<li>przezroczyste dla klienta i serwera</li>
			<li>operujące na wygenerowanej stronie (HTML, CSS, JS)</li>
			<li>konkatenujące i minifikujące CSS-y i JS-y (dla uproszczenia np. tylko w <code>&lt;head&gt;</code>)</li>
			<li>cache'ujące wyniki pracy</li>
		</ul>
	</section>

	<section class="slide">
		<h2>Zadania</h2>
		<h3>Proxy v3</h3>
		<ul>
			<li>przezroczyste dla klienta i serwera</li>
			<li>optymalizacja dla mobile'ów</li>
			<li>skalowanie i zmniejszanie jakości grafik</li>
			<li>wywalenie flashy</li>
			<li>coś jeszcze?</li>
		</ul>
	</section>

	<section class="slide">
		<h2>Zadania</h2>
		<h3>Inne (może ciekawe)</h3>
		<ul>
			<li>walidacja, sesja, autoryzacja</li>
			<li>cache po stronie klienta &ndash; np. proxy cache'ujące wychodzące Ajaksowe zapytania</li>
		</ul>
	</section>

	<section class="slide">
		<h1>Do roboty!</h1>
		<img src="imgs/IMG_2772.jpg" alt="fight!" width="600">
	</section>

	<!--
	<section class="slide">
		<h2></h2>
		<ul>
			<li></li>
		</ul>
	</section>

<pre><?= f("
") ?></pre>

	-->

	<a href="." class="deck-permalink" title="Permalink to this slide">#</a>
	<a href="#" class="deck-prev-link" title="Previous">&#8592;</a>
	<a href="#" class="deck-next-link" title="Next">&#8594;</a>

	<p class="deck-status">
		<span class="deck-status-current"></span>
		/
		<span class="deck-status-total"></span>
	</p>


	<script src="jquery-1.6.4.min.js"></script>
	<script src="modernizr.custom.js"></script>
	<script src="sh/scripts/shCore.js"></script>
	<script src="sh/scripts/shBrushJScript.js"></script>
	<script src="deck.core.js"></script>
	<script src="extensions/menu/deck.menu.js"></script>
	<script src="extensions/goto/deck.goto.js"></script>
	<script src="extensions/status/deck.status.js"></script>
	<script src="extensions/navigation/deck.navigation.js"></script>
	<script src="extensions/hash/deck.hash.js"></script>
	<script>
		$(function() {
			$.deck('.slide');
			$('pre').addClass('brush: js');
			SyntaxHighlighter.all();
		});
	</script>
</body>
</html>
