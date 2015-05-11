
/*
 * This test file tests device registration and
 * transactions made in this process.
 *
 * The tests include error messages as well
 */

var gateway = 'http://gateway.walkielockie',
	sha1    = require('../www/res/js/sha1.js'),
	assert  = require('assert'),
	request = require('supertest'),
	payload;

describe('Device', function() {

	/* Start the registration
	 */
	describe('registering', function() {
		it('returns with JSON', function(done) {

			var data = {},
				str = JSON.stringify(data);

			request(gateway)
				.post('/device/register')
				.send(str)
				.expect(200, function(err, res) {
					done();
					// prepare for next step
					payload = JSON.parse(res.text);
					console.log( '\tTicket ID: \t'+ payload.ticket );
					console.log( '\tDevice ID: \t'+ payload.device );
					console.log( '\tSecret: \t'+ payload.secret );

					GLOBAL.payload = payload;
				});
		});
	});

	/* Testing bad authentication
	 */
	describe('testing bad authentication', function() {
		it('responds with JSON', function(done) {
			
			var data = {
					ticket: payload.ticket,
					authentication: 'foo-bar'
				},
				str = JSON.stringify(data);

			request(gateway)
				.post('/device/register-ack')
				.send(str)
				.expect(200, function(err, res) {
					done();
					// check response
					var resp = JSON.parse(res.text);
					if (resp.error === 510) {
						console.log( '\t-> Correct error code on bad "authentication": '+ resp.error );
					}
				});
		});
	});

	/* Testing bad ticket (1)
	 */
	describe('testing bad authentication', function() {
		it('responds with JSON', function(done) {
			
			var data = {
					ticket: 'foo-bar',
					authentication: (payload.device + payload.secret).sha1()
				},
				str = JSON.stringify(data);

			request(gateway)
				.post('/device/register-ack')
				.send(str)
				.expect(200, function(err, res) {
					done();
					// check response
					var resp = JSON.parse(res.text);
					if (resp.error === 511) {
						console.log( '\t-> Correct error code on bad "ticket_id" (1): '+ resp.error );
					}
				});

		});
	});

	/* Testing bad ticket (2)
	 */
	describe('testing bad authentication', function() {
		it('responds with JSON', function(done) {
			
			var data = {
					ticket: 'WL:1foo-bar',
					authentication: (payload.device + payload.secret).sha1()
				},
				str = JSON.stringify(data);

			request(gateway)
				.post('/device/register-ack')
				.send(str)
				.expect(200, function(err, res) {
					done();
					// check response
					var resp = JSON.parse(res.text);
					if (resp.error === 513) {
						console.log( '\t-> Correct error code on bad "ticket_id" (2): '+ resp.error );
					}
				});

		});
	});

	/* Complete the registration
	 */
	describe('registration ACK', function() {
		it('responds with JSON', function(done) {
			
			var data = {
					ticket: payload.ticket,
					authentication: (payload.device + payload.secret).sha1()
				},
				str = JSON.stringify(data);

			request(gateway)
				.post('/device/register-ack')
				.send(str)
				.expect(200, function(err, res) {
					done();
					//console.log( res.text );
					// check response
					var resp = JSON.parse(res.text);
					console.log( '\tDevice registered: \t'+ payload.device );
				});

		});
	});

	/* Unregister device
	 */
	describe('unregistration', function() {
		it('responds with JSON', function(done) {
			
			var data = {
					device: payload.device,
					authentication: (payload.device + payload.secret).sha1()
				},
				str = JSON.stringify(data);

			request(gateway)
				.post('/device/unregister')
				.send(str)
				.expect(200, function(err, res) {
					done();
					//console.log( res.text );
				});

		});
	});

	/* Remove device from DB
	 */
	describe('is being removed,', function() {
		it('responds with JSON', function(done) {
			
			var data = {
					device: payload.device,
					authentication: (payload.device + payload.secret).sha1()
				},
				str = JSON.stringify(data);

			request(gateway)
				.post('/device/remove')
				.send(str)
				.expect(200, function(err, res) {
					done();
					//console.log( res.text );
					// check response
					console.log( '\tDevice removed: \t'+ payload.device +'\n\n' );
				});

		});
	});

});


