
/*
 * This test file tests device registration and
 * transactions made in this process.
 *
 * The tests include error messages as well
 *
 */

// get sha1 module
var sha1    = require('../www/res/js/sha1.js'),
	domain  = 'http://defiantjs.com/',
	gateway = 'http://gateway.walkielockie/index.php',
	ticket;

// go to domain
casper.start(domain, function() {
	this.echo('Testing from domain: '+ domain);
});

// initiate registration
casper.thenOpen(gateway, {
	method: 'POST',
	data: {
		channel : 'app',
		action  : 'Register_Device'
	}
}, function() {
	this.echo('Requesting to register device.');
	ticket = JSON.parse(this.page.plainText);
});

casper.thenOpen(domain, function() {
	// prepare authentication code
	ticket.authentication = (ticket.ticket_id + ticket.secret).sha1();
	this.echo('Registration authentication: '+ ticket.authentication);


	// ERROR Testing: bad authentication
	this.thenOpen(gateway, {
		method: 'POST',
		data: {
			channel        : 'app',
			action         : 'Register_ACK',
			ticket_id      : ticket.ticket_id,
			authentication : 'foo-bar authentication'
		}
	}, function() {
		var payload = JSON.parse(this.page.plainText);
		if (payload.error === '510') {
			casper.echo(' -> Correct error code on bad "authentication".');
		}
	});

	// ERROR Testing: bad ticket_id
	this.thenOpen(gateway, {
		method: 'POST',
		data: {
			channel        : 'app',
			action         : 'Register_ACK',
			ticket_id      : 'foo-bar ticket',
			authentication : ticket.authentication
		}
	}, function() {
		var payload = JSON.parse(this.page.plainText);
		if (payload.error === '511') {
			casper.echo(' -> Correct error code on bad "ticket_id" (1).');
		}
	});

	// ERROR Testing: bad ticket_id (second test)
	this.thenOpen(gateway, {
		method: 'POST',
		data: {
			channel        : 'app',
			action         : 'Register_ACK',
			ticket_id      : 'WL:1foo-bar',
			authentication : ticket.authentication
		}
	}, function() {
		var payload = JSON.parse(this.page.plainText);
		if (payload.error === '503') {
			casper.echo(' -> Correct error code on bad "ticket_id" (2).');
		}
	});

	// complete registration
	this.thenOpen(gateway, {
		method: 'POST',
		data: {
			channel        : 'app',
			action         : 'Register_ACK',
			ticket_id      : ticket.ticket_id,
			authentication : ticket.authentication
		}
	}, function() {
		casper.echo('Device registered successfully.');
	});

});

casper.run();
