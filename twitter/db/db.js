'use strict';

var mongoose = require('mongoose'),
	schemas = {
		Status: require('./schemas/Status'),
		User: require('./schemas/User')
	},
	db = null,
	deferred = require('deferred');

var registerModels = function () {
	for (var n in schemas) {
		db.model(n, schemas[n]);
	}
};

module.exports = function (dsn, callback) {
	db = mongoose.createConnection(dsn, function (err) {
		if (err) return console.log('DB connection error: ' + err.message);
		registerModels();
		callback(db);
	});
};
