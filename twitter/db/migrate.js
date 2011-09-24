'use strict';

var db = null,
	csv = require('csv'),
	deferred = require('deferred');

var csv2Array = function (fname) {
	var a = [],
		d = deferred();
	csv()
	.fromPath(fname)
	.on('data', function (data, i) { a.push(data); })
	.on('end', function () { d.resolve(a); });

	return d.promise;
};

var runMigration = function () {
	deferred.all(
		csv2Array(__dirname + '/../data/statuses_50k.csv'),
		csv2Array(__dirname + '/../data/users.csv'),
		csv2Array(__dirname + '/../data/followers.csv')
	)
	(function (datas) {
		var statuses = datas[0],
			users = datas[1],
			followers = datas[2],
			User = db.model('User'),
			Status = db.model('Status'),
			ps = [];
	
		console.log('Inserting ' + users.length + ' users');
		users.forEach(function (u) {
			var d = deferred();
			ps.push(d.promise);
			(new User).fromJSON(u).save(function (err) {
				//if (err) console.log(err.message);
				d.resolve();
			});
		});
		console.log('Finished inserting users');

		console.log('Inserting ' + statuses.length + ' statuses');
		statuses.forEach(function (s) {
			var d = deferred();
			ps.push(d.promise);
			(new Status).fromJSON(s).save(function (err) {
				if (err) console.log(err.message);
				d.resolve();
			});
		});
		console.log('Finished inserting statuses');

		deferred.all.apply(deferred, ps)
		(function () {
			console.log('Inserting ' + followers.length + ' followers');

			var acc = [];
			followers.forEach(function (f) {
				if (!acc[f[0]]) acc[f[0]] = [];
				acc[f[0]].push(f[1]);
			});

			acc.forEach(function (fs, user_id) {
				if (!fs || fs.length === 0) return;

				User.findOne({ user_id: user_id }, function (err, user) {
					if (err) return console.log(err);
					if (!user) return;

					User.find({ user_id: { '$in': fs } }, function (err, users) {
						if (err) return console.log(err);
						user.following = users;
						user.save(function (err) {
							if (err) console.log(err);
						});
					});
				});
			});

			console.log('Finished inserting followers');
		}).end();
	})
	.end(function (err) {
		console.log(err);
	});
};

require('./db')('mongodb://127.0.0.1:27017/twitter', function (_db) {
	db = _db;
	runMigration();
});
