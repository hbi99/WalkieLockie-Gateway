
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
	payload;

describe('Domain', function() {

	/* Testing error code 516: (unresolvable domain)
	 */
	describe('register, unresolvable domain', function() {
		it('responds with JSON', function(done) {
			
			request(gateway)
				.post('/domain/register')
				.send(JSON.stringify({
					name     : 'DefiantJS',
					domain   : 'www.defiantjs1.com',
					favicon  : '/path_to_icon.png',
					callback : '/wl_test.php'
				}))
				.expect(200, function(err, res) {
					done();
					// check response
					var resp = JSON.parse(res.text);
					if (resp.error === 516) {
						console.log( '\t-> Correct error code on unresolvable domain: '+ resp.error );
					}
				});
		});
	});

	/* Testing error code 522: (callback missing file)
	 */
	describe('register, missing callback file', function() {
		it('responds with JSON', function(done) {
			
			request(gateway)
				.post('/domain/register')
				.send(JSON.stringify({
					name     : 'DefiantJS',
					domain   : 'www.defiantjs.com',
					favicon  : '/path_to_icon.png',
					callback : '/wl_test1.php'
				}))
				.expect(200, function(err, res) {
					done();
					// check response
					var resp = JSON.parse(res.text);
					if (resp.error === 521) {
						console.log( '\t-> Correct error code on missing callback file: '+ resp.error );
					}
				});
		});
	});

	/* Testing error code 509: (missing parameter 'callback')
	 */
	describe('register, missing parameters', function() {
		it('responds with JSON', function(done) {
			
			request(gateway)
				.post('/domain/register')
				.send(JSON.stringify({
					domain   : 'www.defiantjs.com',
					callback : ''
				}))
				.expect(200, function(err, res) {
					done();
					// check response
					var resp = JSON.parse(res.text);
					if (resp.error === 509) {
						console.log( '\t-> Correct error code on missing parameter (callback): '+ resp.error );
					}
				});
		});
	});

	/* Register domain
	 */
	describe('registering', function() {
		it('returns with JSON', function(done) {

			var temp = Date.now();

			request(gateway)
				.post('/domain/register')
				.send(JSON.stringify({
					name     : 'DefiantJS',
					domain   : 'www.defiantjs.com',
					favicon  : '/path_to_icon.png',
					callback : '/wl_test.php',
					ticket   : temp
				}))
				.expect(200, function(err, res) {
					done();

					payload = JSON.parse(res.text);
					// for debug purposes
					//console.log( JSON.stringify(payload, false, '\t') );
				});

		});
	});

	/* Re-new secret
	 */
	describe('renewing secret', function() {
		it('returns with JSON', function(done) {

			var ticket = Date.now();

			request(gateway)
				.post('/domain/renew-secret')
				.send(JSON.stringify({
					ticket         : ticket,
					ID             : payload.ID,
					callback       : '/wl_test.php',
					authentication : (ticket + payload.secret).sha1()
				}))
				.expect(200, function(err, res) {
				//	done();

					payload = JSON.parse(res.text);
					console.log( JSON.stringify(payload, false, '\t') );
					process.exit(1);

				//	payload = JSON.parse(res.text);
				//	// for debug purposes
				//	console.log( JSON.stringify(payload, false, '\t') );
				});

		});
	});

	/* Testing error code 517: (Domain already registered)
	 */
	describe('register, missing parameters', function() {
		it('responds with JSON', function(done) {
			
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
					// check response
					var resp = JSON.parse(res.text);
					if (resp.error === 517) {
						console.log( '\t-> Correct error code on domain already registered: '+ resp.error );
					}
				});
		});
	});

	/* Unregister domain
	 */
	describe('unregistering', function() {
		it('returns with JSON', function(done) {

			request(gateway)
				.post('/domain/unregister')
				.send(JSON.stringify({
					name           : 'DefiantJS',
					domain         : 'www.defiantjs.com',
					favicon        : '/path_to_icon.png',
					callback       : '/wl_test.php',
					ID             : payload.ID,
					authentication : (payload.ID + payload.secret).sha1()
				}))
				.expect(200, function(err, res) {
					done();
					// check response
					var resp = JSON.parse(res.text);
					console.log( '\t-> Domain unregistered: '+ resp.domain );
				});

		});
	});

});


