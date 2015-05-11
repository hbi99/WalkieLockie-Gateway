
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
					callback : '/path_to_file.php'
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

			request(gateway)
				.post('/domain/register')
				.send(JSON.stringify({
					name     : 'DefiantJS',
					domain   : 'www.defiantjs.com',
					favicon  : '/path_to_icon.php',
					callback : '/path_to_file.php'
				}))
				.expect(200, function(err, res) {
					done();

					payload = JSON.parse(res.text);
					// for debug purposes
					console.log( JSON.stringify(payload, false, '\t') );
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
					favicon  : '/path_to_icon.php',
					callback : '/path_to_file.php'
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
					domain         : 'www.defiantjs.com',
					callback       : '/path_to_file.php',
					id             : payload.ID,
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

	/* Testing error code 518: (Unrecognized domain)
	 */
	describe('unregister, with foo-domain', function() {
		it('responds with JSON', function(done) {
			
			request(gateway)
				.post('/domain/unregister')
				.send(JSON.stringify({
					domain         : 'www.defiantjs1.com',
					callback       : '/path_to_file.php',
					id             : payload.ID,
					authentication : (payload.ID + payload.secret).sha1()
				}))
				.expect(200, function(err, res) {
					done();
					// check response
					var resp = JSON.parse(res.text);
					if (resp.error === 518) {
						console.log( '\t-> Correct error code on missing parameter (callback): '+ resp.error );
					}
				});
		});
	});

});


