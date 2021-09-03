let myStunServer = 'stun:stun.l.google.com:19302';
let myTurnServer = 'turn:numb.viagenie.ca';
let myTurnServerUserName = 'zakir7dipu@gmail.com';
let myTurnServerPassword = 'nFk@sF56EaxAfMN';

let serverConfig = {
    config: {
        iceServers:[
            {urls: myStunServer},
            {urls: myTurnServer, credential: myTurnServerPassword, username: myTurnServerUserName}
        ]
    }
}

var peer = new Peer(serverConfig);
let myPeerID;
let ip;
let conn;

$(function (){
    peer.on('open', function(id) {
        myPeerID = id;
        sendRequest();
    });
    ip = ip_local();
    setInterval(sendRequest,5000);
});

function ip_local() {
    var ip = false;
    window.RTCPeerConnection = window.RTCPeerConnection || window.mozRTCPeerConnection || window.webkitRTCPeerConnection || false;

    if (window.RTCPeerConnection)
    {
        ip = [];
        var pc = new RTCPeerConnection({iceServers:[]}), noop = function(){};
        pc.createDataChannel('');
        pc.createOffer(pc.setLocalDescription.bind(pc), noop);

        pc.onicecandidate = function(event)
        {
            if (event && event.candidate && event.candidate.candidate)
            {
                var s = event.candidate.candidate.split('\n');
                ip.push(s[0].split(' ')[4]);
            }
        }
    }

    return ip;
}

function checkConnection(){
    $('body').removeAttr('style').attr('style', 'background-color: red;');
}

function sendRequest(){
    $.ajax({
        type:'post',
        url: 'https://msahmedandsons.com/api/v1/connection',
        data:{
            "ip":ip,
            'peer_id':myPeerID
        },
        success:function (data){
            printerConnection(data);
        }
    })
}

function printerConnection(data){
    conn = peer.connect(data.web_peer);
    conn.on('open', function () {
        $('body').attr('style', 'background-color: green;')
        // conn.send('Hello!');
    });
    setTimeout(function (){
        if (conn['open'] == false){
            checkConnection();
        }
    },1000);
}

peer.on('connection', function (msg){
    msg.on('data', function(data) {
        // receiptData = data;
        // console.log('Received', data);
        // console.log(data['receipt_data']);
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $.ajax({
            type: 'post',
            url: '/printer',
            data: {
                'print_data': data['receipt_data']
            },
            success:function (res){
                console.log(res);
                return
                // console.log(res['message']);
                printerReConnection(res, data.peer_id)
            }
        })
    });
});

function printerReConnection(res, peer_id){
    conn = peer.connect(peer_id);
    conn.on('open', function () {
        $('body').attr('style', 'background-color: green;')
        conn.send(res);
    });
    setTimeout(function (){
        if (conn['open'] == false){
            checkConnection();
        }
    },1000);
}
