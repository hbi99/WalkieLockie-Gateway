
/*
 * This test file tests domain registration and
 * transactions made in this process.
 *
 * The tests include error messages as well
 */

var gateway = 'http://gateway.walkielockie',
	sha1    = require('../www/res/js/sha1.js'),
	assert  = require('assert'),
	request = require('supertest'),
	TICKET,
	payload,
	DOMAIN,
	DEVICE;

describe('Transaction', function() {

	/* Register DOMAIN
	 */
	describe('registering DOMAIN', function() {
		it('returns with JSON', function(done) {

			request(gateway)
				.post('/domain/register')
				.send(JSON.stringify({
					name     : 'DefiantJS',
					domain   : 'www.defiantjs.com',
					favicon  : '/path_to_icon.png',
					callback : '/wl_test.php'
				}))
				.expect(200, function(err, res) {
					done();
					// prepare for next step
					DOMAIN = JSON.parse(res.text);
				});

		});
	});

	/* Register DEVICE
	 */
	describe('registering DEVICE', function() {
		it('responds with JSON', function(done) {
			
			request(gateway)
				.post('/device/register')
				.send(JSON.stringify({}))
				.expect(200, function(err, res) {
					done();
					// prepare for next step
					DEVICE = JSON.parse(res.text);
				});

		});
	});

	/* Complete DEVICE registration
	 */
	describe('registration DEVICE ACK', function() {
		it('responds with JSON', function(done) {
			
			request(gateway)
				.post('/device/register-ack')
				.send(JSON.stringify({
					ticket         : DEVICE.ticket,
					authentication : (DEVICE.ID + DEVICE.secret).sha1()
				}))
				.expect(200, function(err, res) {
					done();

					payload = JSON.parse(res.text);
					if (payload.code === 601) {
						// check response
						console.log( '\tDevice registered: \t'+ DEVICE.ID );
					}
				});

		});
	});

	/* Get QR code
	 */
	describe('requesting QR code', function() {
		it('returns with JSON', function(done) {

			request(gateway)
				.post('/domain/qr')
				.send(JSON.stringify({
					callback       : '/wl_test.php',
					function       : 'fn_test',
					id             : DOMAIN.ID,
					authentication : (DOMAIN.ID + DOMAIN.secret).sha1()
				}))
				.expect(200, function(err, res) {
					done();

					TICKET = JSON.parse(res.text);
					console.log( '\tTicket QR code: \t'+ TICKET.qr );
				});

		});
	});

	/* DEVICE forwards QR code
	 */
	describe('DEVICE forwarding QR code', function() {
		it('returns with JSON', function(done) {

			var now = Date.now();

			request(gateway)
				.post('/device/qr-code')
				.send(JSON.stringify({
					timestamp      : now,
					qr             : TICKET.qr,
					device         : DEVICE.ID,
					authentication : (now + TICKET.qr + DEVICE.secret).sha1()
				}))
				.expect(200, function(err, res) {
					done();

					//console.log( res.text );
					payload = JSON.parse(res.text);
					//console.log( payload );
					console.log( '\tDomain response: \t'+ payload.response );
				});

		});
	});

	/* Remove device from DB
	 */
	describe('removing DEVICE,', function() {
		it('responds with JSON', function(done) {
			
			request(gateway)
				.post('/device/remove')
				.send(JSON.stringify({
					device         : DEVICE.ID,
					authentication : (DEVICE.ID + DEVICE.secret).sha1()
				}))
				.expect(200, function(err, res) {
					done();
					// check response
					console.log( '\tDevice removed: \t'+ DEVICE.ID );
				});

		});
	});

	/* Unregister DOMAIN
	 */
	describe('unregistering DOMAIN', function() {
		it('returns with JSON', function(done) {

			request(gateway)
				.post('/domain/unregister')
				.send(JSON.stringify({
					name           : DOMAIN.name,
					domain         : DOMAIN.domain,
					favicon        : DOMAIN.favicon,
					callback       : '/wl_test.php',
					id             : DOMAIN.ID,
					authentication : (DOMAIN.ID + DOMAIN.secret).sha1()
				}))
				.expect(200, function(err, res) {
					done();
					//console.log(res.text);
					// check response
					var resp = JSON.parse(res.text);
					console.log( '\t-> Domain unregistered: '+ resp.domain );
				});

		});
	});

});

