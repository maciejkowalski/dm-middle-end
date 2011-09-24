'use strict';

var mongoose = require('mongoose'),
	Schema = mongoose.Schema;

var User = new Schema({
	user_id: { type: Number, required: true, unique: true },
	name: { type: String },
	screen_name: { type: String, required: true, unique: true },
	following: [User]
});

User.methods.fromJSON = function (obj) {
	this.user_id = obj[0];
	this.name = obj[1];
	this.screen_name = obj[2];

	return this;
};

module.exports = User;
