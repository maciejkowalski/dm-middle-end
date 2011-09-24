require('supervisor').run([
	'-w', 'service.js,db',
	'-x', 'node4',
	'service.js'
]);
