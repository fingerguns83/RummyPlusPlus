import * as FiveCrowns from './main.js';
import * as faker from '@faker-js/faker';


let timeout = 0; // declare outside websocket function
//export let conn = new WebSocket('wss://serv.rummyplusplus.com');
export let conn = new WebSocket('ws://localhost:8988');
//export let conn = new WebSocket('ws://10.0.0.8:8988');
var timeoutInterval;


function resetAndReconnect() {
    clearInterval(timeoutInterval); // stop the interval
    timeout = 0; // reset timeout
    conn.close(); // close current connection
    conn = new WebSocket('wss://serv.rummyplusplus.com'); // reconnect
}

function checkTimeout(){
    timeout++; // increment the timeout by 1
    if (timeout > 120) {
        console.log(timeout); // to see the increment in console
        resetAndReconnect();
    }
}


conn.onopen = function(){
    timeoutInterval = setInterval(checkTimeout, 1000);
    console.log("WebSocket connection opened!");

}
conn.onmessage = function(e) {
    console.log(e.data);
    var msg = JSON.parse(e.data);
    switch (msg.type){
        case "info":
            FiveCrowns.handleInfoMessage(msg);
            break;
        case "action":
            FiveCrowns.handleActionMessage(msg);
            break;
        case "state":
            FiveCrowns.handleStateMessage(msg);
            break;
        case "ping":
            timeout = 0;
            break;
    }
}
conn.onerror = function(e) {
    console.error("WebSocket error observed:", e);
};




