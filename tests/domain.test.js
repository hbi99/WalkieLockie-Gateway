
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

	/* Testing missing parameters
	describe('register, unresolvable domain', function() {
		it('responds with JSON', function(done) {
			
			request(gateway)
				.post('/domain/register')
				.send(JSON.stringify({
					action   : 'register',
					name     : 'DefiantJS',
					domain   : 'www.defiantjs1.com',
					callback : '/path_to_file.php'
				}))
				.expect(200, function(err, res) {
					done();
					// check response
					var resp = JSON.parse(res.text);
					if (resp.error === '516') {
						console.log( '\t-> Correct error code on unresolvable domain: '+ resp.error );
					}
				});
		});
	});
	 */

	/* Testing missing parameters
	describe('register, missing parameters', function() {
		it('responds with JSON', function(done) {
			
			request(gateway)
				.post('/domain/register')
				.send(JSON.stringify({
					action   : 'register',
					domain   : 'www.defiantjs.com',
					callback : ''
				}))
				.expect(200, function(err, res) {
					done();
					// check response
					var resp = JSON.parse(res.text);
					if (resp.error === '509') {
						console.log( '\t-> Correct error code on missing parameter (callback): '+ resp.error );
					}
				});
		});
	});
	 */

	/* Register domain
	 */
	describe('registering', function() {
		it('returns with JSON', function(done) {

			request(gateway)
				.post('/domain/register')
				.send(JSON.stringify({
					action   : 'register',
					name     : 'DefiantJS',
					domain   : 'www.defiantjs.com',
					favicon  : '/path_to_icon.php',
					callback : '/path_to_file.php'
				}))
				.expect(200, function(err, res) {
					done();

					GLOBAL.payload = JSON.parse(res.text);
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
					action         : 'unregister',
					name           : 'DefiantJS',
					domain         : 'www.defiantjs.com',
					account        : GLOBAL.payload.account,
					authentication : (GLOBAL.payload.account + GLOBAL.payload.secret).sha1()
				}))
				.expect(200, function(err, res) {
					done();

					console.log( res.text );
				});

		});
	});

});


