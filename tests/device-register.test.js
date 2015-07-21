
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

			request(gateway)
				.post('/device/register')
				.send(JSON.stringify({}))
				.expect(200, function(err, res) {
					done();
					// prepare for next step
					payload = JSON.parse(res.text);
					console.log( '\tTicket ID: \t'+ payload.ticket );
					console.log( '\tDevice ID: \t'+ payload.ID );
					console.log( '\tSecret: \t'+ payload.secret );
				});
		});
	});

	/* Testing bad authentication
	 */
	describe('testing bad authentication', function() {
		it('responds with JSON', function(done) {
			
			request(gateway)
				.post('/device/register-ack')
				.send(JSON.stringify({
					ticket         : payload.ticket,
					authentication : 'foo-bar'
				}))
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
			
			request(gateway)
				.post('/device/register-ack')
				.send(JSON.stringify({
					ticket         : 'foo-bar',
					authentication : (payload.ID + payload.secret).sha1()
				}))
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
			
			request(gateway)
				.post('/device/register-ack')
				.send(JSON.stringify({
					ticket         : 'WL:1foo-bar',
					authentication : (payload.ID + payload.secret).sha1()
				}))
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
			
			request(gateway)
				.post('/device/register-ack')
				.send(JSON.stringify({
					ticket         : payload.ticket,
					authentication : (payload.ID + payload.secret).sha1()
				}))
				.expect(200, function(err, res) {
					done();
					//console.log( res.text );
					// check response
					var resp = JSON.parse(res.text);
					console.log( '\tDevice registered: \t'+ payload.ID );
				});

		});
	});

	/* Re-new secret
	 */
	describe('renew secret', function() {
		it('responds with JSON', function(done) {
			
			var ticket = 'WL:'+ Date.now();

			request(gateway)
				.post('/device/renew-secret')
				.send(JSON.stringify({
					device         : payload.ID,
					ticket         : ticket,
					authentication : (ticket + payload.secret).sha1()
				}))
				.expect(200, function(err, res) {
					var resp = JSON.parse(res.text),
						check = (ticket + payload.secret + resp.secret).sha1();
					if (resp.authentication !== check) {
						console.log( 'Response can not be authenticated'.red );
					} else {
						// update secret
						payload.secret = resp.secret;
						done();
						console.log( '\tDevice secret re-newed: '+ resp.secret );
					}
				});

		});
	});

	/* Unregister device
	 */
	describe('unregistration', function() {
		it('responds with JSON', function(done) {
			
			request(gateway)
				.post('/device/unregister')
				.send(JSON.stringify({
					device         : payload.ID,
					authentication : (payload.ID + payload.secret).sha1()
				}))
				.expect(200, function(err, res) {
					var resp = JSON.parse(res.text);
					if (resp.error) {
						console.log( (resp.description +' ('+ resp.error +')').red );
						process.exit(1);
					} else {
						done();
						console.log( '\tDevice unregistered' );
					}
				});

		});
	});

	/* Remove device from DB
	 */
	describe('is being removed,', function() {
		it('responds with JSON', function(done) {
			
			request(gateway)
				.post('/device/remove')
				.send(JSON.stringify({
					device         : payload.ID,
					authentication : (payload.ID + payload.secret).sha1()
				}))
				.expect(200, function(err, res) {
					done();
					// check response
					console.log( '\tDevice removed: \t'+ payload.ID +'\n\n' );
				});

		});
	});

});


