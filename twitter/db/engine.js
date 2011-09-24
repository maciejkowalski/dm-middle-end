'use strict';

var deferred = require('deferred'),
	LIMIT = 20;

module.exports = function (dsn, callback) {
	require('./db')(dsn, function (db) {
		callback(new Engine(db));
	});
};

var Engine = function (db) {
	this._db = db;
	this._user = db.model('User');
	this._status = db.model('Status');
};

Engine.prototype.postTweet = function (screen_name, text) {
	var d = deferred(),
		that = this;

	that.getUser(screen_name)
	(function (user) {
		var status = new that._status();
		status.text = text;
		status.user_id = user.user_id;
		status.save(function (err) {
			if (err) return d.resolve(err);
			d.resolve(status);
		});
	}).end(function (err) {
		d.resolve(err);
	});

	return d.promise;
};

Engine.prototype.getUserTimeline = function (screen_name, offset) {
	var d = deferred(),
		that = this;
	
	that.getUser(screen_name)
	(function (user) {
		var users_ids = [ user.user_id ];
		//damn... why Mongoose does not load embedded docs?
		that._user.find({ _id: { '$in': user.following } }, function (err, users) {
			if (err) return d.resolve(err);

			users.forEach(function (u) {
				users_ids.push(u.user_id);
			});

			that._status.find({
				user_id: { '$in': users_ids }
			})
			.limit(LIMIT)
			.sort( 'created_at', -1)
			.skip(offset || 0)
			.exec(function (err, statuses) {
				d.resolve(err || prepareStatuses(statuses, users.concat(user)));
			});
		});

	}).end(function (err) {
		d.resolve(err);
	});

	return d.promise;
};

Engine.prototype.getUser = function (screen_name) {
	var d = deferred(),
		that = this;

	that._user.findOne({ screen_name: screen_name }, function (err, user) {
		if (err) return d.resolve(err);
		if (!user) return d.resolve(new Error('Wrong screen_name: "' + screen_name + '"'));

		d.resolve(user);
	});

	return d.promise;
};	

var prepareStatuses = function (statuses, users) {
	return {
		statuses: statuses,
		users: users
	};
};
