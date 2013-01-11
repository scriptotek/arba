
var spin = false;

function tbl_click(e) {
    var link = $(this),
        user = '';
    e.preventDefault();
    if (link.hasClass('user')) {
        // clicked a user
        if (link.find('em').length === 1) {
            user = '';
        } else {
            user = link.text();
        }
        console.log('get-user-report:'+user);
        $('#user').val(user);
        get_tt();
        //$.getJSON('./', {
        //    'get': 'report', 
        //    'user': user,
        //    'year': $('#year').val(),
        //    'month': $('#month').val()
        //}, got_tt);
    } else if (link.hasClass('forventet')) {
        alert('Tja, hva skal skje nå?');
    }
}

function got_tt(res) {
    var tab = $('table.main');
    tab.html('');
    console.log(res);
    if (res.events) {
        var tot = 0.;
        tab.append('<tr><th>Dato</th><th class="center">Timer</th><th>Merknad</th></tr>');
        $.each(res.events, function(index, event) {
            var note = '',
                start = Date.parse(event.start).toString('dddd d. MMM, kl. HH.mm'),
                end = Date.parse(event.end).toString('HH.mm'),
                duration = event.duration;
            tot += duration;
            if (event.summary !== event.user) {
                note = event.summary;
            }
            tab.append('<tr><td class="right">' + start + '–' + end + '</td><td class="center">' + duration + '</td><td>' + note + '</td></tr>');
        });
        forventet = 0;
        balanse = tot - forventet;
        if (balanse > 0) balanse = '+ ' + balanse;
        else if (balanse < 0) balanse = balanse;
        else balanse = '0';

        tab.append('<tr class="sum"><td class="right">Totalt registrert</td><td class="center">' + tot + '</td><td></td></tr>');
        tab.append('<tr class="sum"><td class="right">Totalt forventet</td><td class="center"><a href="#" class="forventet">' + forventet + '</a></td><td></td></tr>');
        tab.append('<tr class="sum"><td class="right">Balanse</td><td class="center">' + balanse + '</td><td></td></tr>');
    } else if (res.users) {
        tab.append('<tr><th>Arbeider</th><th class="center">Timer</th></tr>');
        var tot = 0;
        var unknown = 0;
        $.each(res.users, function(user, hours) {
            if (user == '') {
                unknown = hours;
            } else {
                tot += hours;
                tab.append('<tr><td><a class="user" href="#">' + user + '</a></td><td class="center">' + hours + '</td></tr>');
            }
        });
        tab.append('<tr class="sum"><td class="left">Totalt knyttet til personer</td><td class="center">' + tot + '</td></tr>');
        tab.append('<tr><td><a class="user" href="#"><em>Ikke knyttet til personer</em></a></td><td class="center">' + unknown + '</td></tr>');
        tab.append('<tr class="sum"><td class="left">Totalt</td><td class="center">' + (tot+unknown) + '</td></tr>');
    }
    $('table.main a').off();
    $('table.main a').on('click', tbl_click);
}

function get_tt() {
    var o = {
        'get': 'report', 
        'user': $('#user').val(),
        'year': $('#year').val(),
        'month': $('#month').val()
    };
    if ($('#enddate').is(':visible')) {
        o.endmonth = $('#endmonth').val();
        o.endyear = $('#endyear').val();
    }
    $.getJSON('./', o, got_tt);
}

function rotation() {
    if (spin) {
       $("#uiologo").rotate({
                 angle:0, 
                 animateTo:360, 
                 callback: rotation
              });
    }
}

$(document).ready(function() {

    $('.spinner')
        .hide()  // hide it initially
        .ajaxStart(function () {
            spin = true;
            //rotation();
            $(this).show();
            $('select').attr('disabled', true);
        })  
        .ajaxStop(function () {
            spin = false;
            $(this).hide();
            $('select').attr('disabled', false);
        }); 

    $.getJSON('./', {'get': 'users'}, function(users) {
        $.each(users, function(index, value) {
            //console.log(value);
            $('#user').append('<option value="' + value + '">' + value + '</option>');
        });
        get_tt();
    });

    $('#month').on('change', get_tt);
    $('#year').on('change', get_tt);
    $('#user').on('change', get_tt);

    $('a.expand').click(function(e) {
        e.preventDefault();
        var $this = $(this),
            prev = $(this).prev();
        $this.hide();
        $('#enddate').show();
        $('#endmonth').on('change', get_tt);
        $('#endyear').on('change', get_tt);
    });
});

