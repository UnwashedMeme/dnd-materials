var scribe = {};

scribe.party = ["Ammonia", "Ryepup", "Tibbar", "Jack", "Ecthellion"];

scribe.nextRound = function(){
    var round = scribe.c.round + 1;
    //    var newRound = $('#x-templates .x-round').clone();
    scribe.c.round = round;
    //$('.x-round.active').removeClass('active');
    //newRound.addClass('active');
    $('#x-round').text(round);
    $('#x-player-container :first').addClass('active');
};

scribe.startCombat = function(){
    $('#x-roll-initiative').fadeOut();
    scribe.c = {
	startTime : new Date(),
	round:-1
    };
    var c = scribe.c;
    //need the string+ to make types work out
    $('#x-start-time').text(c.startTime + "");
    $('#x-combat-summary').fadeIn();
    
    $('#x-player-container').empty();
    scribe.c.party = [];
    var partyInits = [];
    var partyDom = {};
    $.each(scribe.party, function(index, p){
	    var px = {
		name : p,
		init : parseInt(prompt("initiative for " + p + "?", Math.round(20*Math.random())))
	    };
	    if(partyDom[px.init]){
		var other = partyDom[px.init];
		var msg = 'Is ' + p + ' faster than '+other.name+'?\n\nOk for '+p+', cancel for '+other.name;
		px.init += confirm(msg) ? 0.1 : -0.1;
	    }
	    partyInits.push(px.init);
	    partyDom[px.init] = px;
	});
    //now that we know the order, put some dom on there
    $.each(partyInits.sort().reverse(), function(i,v){
	    //clone the player template
	    var dom = $('#x-templates .x-player').clone();
	    $.each(partyDom[v], function(k,v){
		    $('.'+k, dom).text(v);
		});
	    $('#x-player-container').append(dom);
	});

    $('#x-player-container').fadeIn();
    //start the round
    scribe.nextRound();
  
    c.durationTimer = window.setInterval(function(){
	    $('#x-duration').text((Math.round((new Date() - scribe.c.startTime) / 1000) / 60) + "m");
	}, 5000);
    
    console.log("starting the combat");
};

scribe.nextPlayer = function(){
    //mark the next player
    $('.x-player .active')
};

scribe.endCombat = function(){
    window.clearInterval(scribe.c.durationTimer);
    $('#x-roll-initiative').fadeIn();
    $('#x-combat-summary').fadeOut();
};

scribe.init = function(){
    console.log('initialized');
    $('#x-roll-initiative').click(scribe.startCombat);
    $('#x-combat-over').click(scribe.endCombat);
    $('*').keypress(function(evt){
	    if(scribe.c){
		switch(evt.charCode){
		case 110:
		    scribe.nextPlayer();
		    break;
		case 112:
		    console.log('prev');
		    break;
		}
	    }
	});
};

$(scribe.init);
