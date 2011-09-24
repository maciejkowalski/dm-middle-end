'use strict';

var restify = require('restify'),
	server = restify.createServer(),
	engine = null;

/*
 * @params
 * screen_name
 * offset (optional)
 */
server.get('/statuses/user_timeline.json', function (req, res) {
	engine.getUserTimeline(req.params.screen_name, req.params.offset)
	(function (statuses) {
		res.send(200, statuses);
	}).end(function (err) {
		res.send(404, err);
	});
});

/*
 * @params
 * screen_name
 * text
 */
server.get('/statuses/update.json', function (req, res) {
	engine.postTweet(
		req.params.screen_name,
		req.params.text
	)
	(function (status) {
		res.send(200, status);
	}).end(function (err) {
		res.send(404, err);
	});
});

require('./db/engine')('mongodb://localhost:27017/twitter', function (_engine) {
	engine = _engine;
	server.listen(8080);
	console.log('Service at :8080');
});
