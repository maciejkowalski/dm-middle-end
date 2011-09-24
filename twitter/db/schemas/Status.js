'use strict';

var mongoose = require('mongoose'),
	Schema = mongoose.Schema,
	ObjectId = Schema.ObjectId;

var Status = new Schema({
	text: { type: String, trim: true, required: true },
	user_id: { type: Number, required: true },
	created_at: { type: Date, required: true, index: true, default: Date.now }
});

Status.methods.fromJSON = function (obj) {
	this.text = obj[1];
	this.user_id = obj[2];
	this.created_at = new Date(obj[3]);

	return this;
};

module.exports = Status;
