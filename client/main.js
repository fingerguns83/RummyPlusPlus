import './style.css'
import {conn} from './socket.js';

export var playerId;
export var playerName = "";
export var discardTop;
export var currentPlayer = false;
export var lastRound = false;
export var books = [];
export var goOutCards = [];
export var tempDiscard;
export var drawn = false;


/* SOUNDS */
var drawSounds = [];
var dealSounds = [];
var discardSounds = [];
var outSound;
var outSoundNotFirst;
var roundStartSound;
var roundEndSound;
var yourTurnSound;
var shuffleSound;

function playRandomDeal() {

    let randomAudio = dealSounds[Math.floor(Math.random() * dealSounds.length)];
    randomAudio.play();
}

function playRandomDraw() {

    let randomAudio = drawSounds[Math.floor(Math.random() * drawSounds.length)];
    randomAudio.play();
}

function playRandomDiscard() {
    let randomAudio = discardSounds[Math.floor(Math.random() * discardSounds.length)];
    randomAudio.play();
}

/*---------*/

$('#start-btn').click(function(){

    drawSounds = [
        new Audio('/assets/sounds/draw_1.mp3'),
        new Audio('/assets/sounds/draw_2.mp3'),
        new Audio('/assets/sounds/draw_3.mp3')
    ];
    dealSounds = [
        new Audio('/assets/sounds/deal_1.mp3'),
        new Audio('/assets/sounds/deal_2.mp3')
    ];
    discardSounds = [
        new Audio('/assets/sounds/discard_1.mp3'),
        new Audio('/assets/sounds/discard_2.mp3')
    ];
    outSound = new Audio('/assets/sounds/player_out.mp3');
    outSoundNotFirst = new Audio('/assets/sounds/player_out_2.mp3');
    roundStartSound = new Audio('/assets/sounds/round_start.mp3');
    roundEndSound = new Audio('/assets/sounds/round_end.mp3');
    yourTurnSound = new Audio('/assets/sounds/your_turn.mp3');
    shuffleSound = new Audio('/assets/sounds/shuffle.mp3');

    $('#app').removeClass("blur");
    $('#start-panel').hide();

    if ($('#playername-input').val() !== ""){
        playerName = $('#playername-input').val();
    }
    else {
        playerName = "Player";
    }

    startGame();
});

function startGame(){
    let response = {
        type: 'info',
        payload: {
            type: 'registration',
            data: {
                "gameId": "",
                "playerName": playerName
            }
        },
    }
    shuffleSound.play().then(function(){
        conn.send(JSON.stringify(response));
    });
}



$('#hand').droppable();
$('#book-builder').droppable({
    accept: ".playingcard",
    tolerance: "intersect",

    drop: function (event, ui) {
        playRandomDiscard();

        var card = ui.draggable;
        card.draggable({
            stop: function () {
            }
        });
        card.detach();
        card.css('top', 0);
        card.css('left', 0);
        card.css('position', 'static');
        //card.css(styles);
        card.appendTo('#book-holder');

        testBookHolder();

        card.click(function () {
            card.detach();
            $('#hand').append(card);

            card.css('top', 0);
            card.css('left', 0);
            card.css('position', 'relative');
            card.draggable({
                stack: ".playingcard",
                containment: "body",
                /*refreshPositions: true,*/
                revert: "invalid",
                scroll: false,
                cursor: "grabbing",
                zIndex: 100,
                stop: function (event, ui) {
                    card.css({
                        'position': 'absolute',
                        'top': ui.helper.offset().top + 'px',
                        'left': ui.helper.offset().left + 'px'
                    });
                }
            });
            card.click(function () {
            });

            testBookHolder();
        });
    }
});

$('#draw1, #draw2').click(function () {
    if (currentPlayer) {
        if (!drawn){
            var message = {
                type: "intent",
                payload: {
                    type: $(this).prop('id'),
                    data: {
                        player: playerId
                    }
                }
            }
            conn.send(JSON.stringify(message))
        }
    }
});

function enableOutButtons() {
    $('#out-btn').removeClass(['bg-opacity-0', 'text-opacity-0', 'border-gray-200']).prop('disabled', false);
    $('#out-btn-lg').removeClass(['bg-opacity-0', 'text-opacity-0', 'border-gray-200']).prop('disabled', false);
}

function disableOutButtons() {
    $('#out-btn').addClass(['bg-opacity-0', 'text-opacity-0', 'border-gray-200']).prop('disabled', true);
    $('#out-btn-lg').addClass(['bg-opacity-0', 'text-opacity-0', 'border-gray-200']).prop('disabled', true);
}

function showBookBuilder(force) {
    $('#discardpile').hide();
    $('#submit-book-btn').hide().prop('disabled', false);

    if (!lastRound) {
        disableOutButtons();
    }

    $('#out-btn, #out-btn-lg').find('p').text("Done");

    $('#book-builder').fadeIn('fast');
    $('#playingfield').addClass('blur-lg');

}

function hideBookBuilder() {
    $('#discardpile').show().prop('disabled', false);
    $('.playingcard').click(function(){});
    $('#out-btn, #out-btn-lg').find('p').text("Go Out");
    $('#book-builder').fadeOut('fast');
    $('#playingfield').removeClass('blur-lg');

}

$('#out-btn, #out-btn-lg').click(function () {
    if (!$(this).prop('disabled')) {
        if ($('#book-builder').is(":hidden")) {
            showBookBuilder(false);
        } else {
            hideBookBuilder();
        }
    }
});

function testBookHolder() {
    var bookholder = $('#book-holder');
    if (bookholder.children().length >= 3) {
        $('#submit-book-btn').show();
    } else {
        $('#submit-book-btn').hide();
    }

    if (bookholder.children().length >= 7) {
        bookholder.addClass('grid-cols-15');
    } else {
        bookholder.removeClass('grid-cols-15');
    }
}

function resetGoOutCards() {
    goOutCards.forEach((el) => {
        $('#hand').append(el);
    });

    books = [];
    goOutCards = [];
}

$('#submit-book-btn').click(function () {
    $('#submit-book-btn').hide();
    let bookArray = [];
    $('#book-holder').children().each(function () {
        var card = $(this);
        card.detach();

        card.css('top', 0);
        card.css('left', 0);
        card.css('position', 'relative');
        card.draggable({
            stack: ".playingcard",
            containment: "body",
            /*refreshPositions: true,*/
            revert: "invalid",
            scroll: false,
            cursor: "grabbing",
            zIndex: 100,
            stop: function (event, ui) {
                card.css({
                    'position': 'absolute',
                    'top': ui.helper.offset().top + 'px',
                    'left': ui.helper.offset().left + 'px'
                });
            }
        });


        goOutCards.push($(this));
        bookArray.push($(this).prop('id').replace("card", ""));
    })
    books.push(bookArray);
    if (lastRound){
        enableOutButtons();
    }
    if ($('#hand').children().length < 4) {
        hideBookBuilder();
    }
});

$('#close-book-builder-btn').click(function () {
    $('#out-btn, #out-btn-lg').find('p').text("Go Out");
    $('#book-holder').find('.playingcard').each(function () {
        var card = $(this);
        card.detach();
        $('#hand').append(card);
        card.css('top', 0);
        card.css('left', 0);
        card.css('position', 'relative');
        card.draggable({
            stack: ".playingcard",
            containment: "body",
            revert: "invalid",
            scroll: false,
            cursor: "grabbing",
            zIndex: 100,
            stop: function (event, ui) {
                card.css({
                    'position': 'absolute',
                    'top': ui.helper.offset().top + 'px',
                    'left': ui.helper.offset().left + 'px'
                });
            }
        });
    });

    hideBookBuilder();

    enableOutButtons();
});


export function addPlayer(playerId, playerName, playerGender, playerScore) {
    let newPlayer = `
    <div id="${playerId}" class="flex w-full h-full">
        <div id="player-name-holder" class="player-name-holder">
            <div class="flex w-full h-full items-center pl-4 border-r-2 border-stone-700">
                <p class="mt-0.5 truncate">${playerName}</p>
            </div>
        </div>
        <div id="player-score-holder" class="player-score-holder">
            <p class="score mt-0.5">${playerScore}</p>
        </div>
    </div>`;
    let newPlayerLg = `
    <div id="${playerId}-lg" class="flex w-full h-full">
        <div class="player-photo-holder-lg">
            <div class="text-stone-700">
                <svg xmlns="http://www.w3.org/2000/svg" width="3em" height="3em" viewBox="0 0 24 24"><path fill="currentColor" d="M13.5 2c0 .444-.193.843-.5 1.118V5h5a3 3 0 0 1 3 3v10a3 3 0 0 1-3 3H6a3 3 0 0 1-3-3V8a3 3 0 0 1 3-3h5V3.118A1.5 1.5 0 1 1 13.5 2M0 10h2v6H0zm24 0h-2v6h2zM9 14.5a1.5 1.5 0 1 0 0-3a1.5 1.5 0 0 0 0 3m7.5-1.5a1.5 1.5 0 1 0-3 0a1.5 1.5 0 0 0 3 0"/></svg>
            </div>
        </div>
        <div class="player-info-holder">
            <div class="player-name-holder-lg">
                <div class="flex w-full h-full justify-center items-center border-b-2 border-stone-700 truncate">${playerName}</div>
            </div>
            <div class="player-score-holder-lg">
                <p class="score">${playerScore}</p>
            </div>
        </div>
    </div>`;

    $('#player-grid-small').append(newPlayer);
    $('#player-grid-lg').append(newPlayerLg);
}

function createScoreboard(msg){
    msg.payload.data.players.forEach((player, index) => {
        let scoreboardLine = `
            <div class="flex w-full my-1 items-center">
              <p class="scoreboard-name flex w-1/2 justify-start pr-4">${player.name}</p>
              <p class="scoreboard-score flex w-1/2 justify-end pl-4">${player.score}</p>
            </div>`;
        let hr = `<hr class="border-stone-700 border-2">`;

        $('#scoreboard-line-holder').append(scoreboardLine);
        if (index !== msg.payload.data.players.length - 1){
            $('#scoreboard-line-holder').append(hr);
        }
    });
    $('#app').addClass('blur');
    $('#scoreboard').show();
}

$('#again-btn').click(function(){
    location.reload();
});

export function createNewCard(id, suit, value) {
    var symbol = '';
    var color = '';
    //var stroke = '; -webkit-text-stroke-width: 1px; -webkit-text-stroke-color: black;'
    var stroke = '';
    if (value === "JOKER") {
        value = "?";
    }
    switch (suit) {
        case "clubs":
            symbol = '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 16 16"><path fill="currentColor" d="M11.5 12.5a3.5 3.5 0 0 1-2.684-1.254a20 20 0 0 0 1.582 2.907c.231.35-.02.847-.438.847H6.04c-.419 0-.67-.497-.438-.847a20 20 0 0 0 1.582-2.907a3.5 3.5 0 1 1-2.538-5.743a3.5 3.5 0 1 1 6.708 0A3.5 3.5 0 1 1 11.5 12.5"/></svg>';
            color = 'rgb(0 178 24)';
            break;
        case "diamonds":
            symbol = '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 16 16"><path fill="currentColor" d="M2.45 7.4L7.2 1.067a1 1 0 0 1 1.6 0L13.55 7.4a1 1 0 0 1 0 1.2L8.8 14.933a1 1 0 0 1-1.6 0L2.45 8.6a1 1 0 0 1 0-1.2"/></svg>';
            color = 'rgb(49 88 225)';
            break;
        case "hearts":
            symbol = '<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"1em\" height=\"1em\" viewBox=\"0 0 16 16\"><path fill=\"currentColor\" d=\"M4 1c2.21 0 4 1.755 4 3.92C8 2.755 9.79 1 12 1s4 1.755 4 3.92c0 3.263-3.234 4.414-7.608 9.608a.513.513 0 0 1-.784 0C3.234 9.334 0 8.183 0 4.92C0 2.755 1.79 1 4 1\"/></svg>';
            color = 'rgb(252 39 22)';
            break;
        case "spades":
            symbol = '<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"1em\" height=\"1em\" viewBox=\"0 0 16 16\"><path fill=\"currentColor\" d=\"M7.184 11.246A3.5 3.5 0 0 1 1 9c0-1.602 1.14-2.633 2.66-4.008C4.986 3.792 6.602 2.33 8 0c1.398 2.33 3.014 3.792 4.34 4.992C13.86 6.367 15 7.398 15 9a3.5 3.5 0 0 1-6.184 2.246a20 20 0 0 0 1.582 2.907c.231.35-.02.847-.438.847H6.04c-.419 0-.67-.497-.438-.847a20 20 0 0 0 1.582-2.907\"/></svg>';
            color = 'black';
            stroke = '';
            break;
        case "stars":
            symbol = '<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"1em\" height=\"1em\" viewBox=\"0 0 16 16\"><path fill=\"currentColor\" d=\"M3.612 15.443c-.386.198-.824-.149-.746-.592l.83-4.73L.173 6.765c-.329-.314-.158-.888.283-.95l4.898-.696L7.538.792c.197-.39.73-.39.927 0l2.184 4.327l4.898.696c.441.062.612.636.282.95l-3.522 3.356l.83 4.73c.078.443-.36.79-.746.592L8 13.187l-4.389 2.256z\"/></svg>';
            color = 'rgb(241,143,0)';
            break;
        default:
            symbol = ""
            color = 'rgb(126 34 206)'
            break;
    }
    return '<div id="card' + id + '" class="playingcard hidden" style="color: ' + color + stroke + '"><div class="flex h-full w-full items-center justify-center py-2 text-3xl lg:text-5xl"><div class="w-full h-full content-evenly px-2"><p class="flex w-full h-1/2 lg:h-1/3 text-center justify-center lg:justify-start items-center lg:items-start"><span class="">' + value + '</span></p><p class="hidden lg:flex w-full h-1/3 text-center justify-center items-center"><span>' + symbol + '</span></p><p class="flex w-full h-1/2 lg:h-1/3 text-center justify-center lg:justify-end items-baseline"><span class="block lg:hidden">' + symbol + '</span><span class="hidden lg:block">' + value + '</span></p></div></div></div>';
}

export function changeDiscard(id, suit, value) {
    var discardPile = $('#discardpile');
    discardTop = id;
    var placeholder = $('#discard-placeholder');
    if (placeholder.is(":visible")) {
        placeholder.hide();
        discardPile.append(createNewCard(id, suit, value));
    } else {
        discardPile.children().not('#discard-placeholder').remove();
        discardPile.append(createNewCard(id, suit, value));
    }
    var card = $('#card' + id);
    card.show().effect('shake', {direction: "down", distance: 5, times: 1});
    card.click(function () {
        if (currentPlayer) {
            if (!drawn){
                var message = {
                    type: "intent",
                    payload: {
                        type: "drawdiscard",
                        data: {
                            player: playerId
                        }
                    }
                }
                conn.send(JSON.stringify(message))
            }
        }
    });
    playRandomDiscard();
}

export function addCardToHand(id, suit, value) {
    $('#hand').append(createNewCard(id, suit, value));
    var card = $('#card' + id);
    card.draggable({
        stack: ".playingcard",
        containment: "body",
        refreshPositions: true,
        revert: "invalid",
        scroll: false,
        cursor: "grabbing",
        zIndex: 100,
        start: function (event, ui){
            card.css('z-index', 100);
        },
        stop: function (event, ui) {
            card.css({
                'position': 'absolute',
                'top': ui.helper.offset().top + 'px',
                'left': ui.helper.offset().left + 'px'
            });
        }
    });
    card.show().effect('shake', {direction: "down", distance: 5, times: 1});
    playRandomDeal();
}

export function dealCards(msg) {
    const delay = ms => new Promise(res => setTimeout(res, ms));

    async function deal() {
        for (const card of msg.payload.data.player.hand) {
            await delay(900); // delay of 1000 ms (1 second)

            addCardToHand(card.id, card.suit, card.value);
        }
    }

    deal().then(() => {
        setTimeout(function () {
            changeDiscard(msg.payload.data.discardPileTop.id, msg.payload.data.discardPileTop.suit, msg.payload.data.discardPileTop.value);
            setTimeout(function () {
                var initAck = {
                    type: 'ack',
                    payload: {
                        type: 'init'
                    }
                }
                conn.send(JSON.stringify(initAck));
            }, 900);
        }, 900);
    });
}

export function displayCurrentPlayer(msg, end = false) {
    var allPlayers = $('#player-grid-small').children();
    var allPlayersLg = $('#player-grid-lg').children();

    var currentOtherPlayer = $('#' + msg.payload.data.currentPlayer);
    var currentOtherPlayerLg = $('#' + msg.payload.data.currentPlayer + "-lg");

    allPlayers.each(function () {
        $(this).find('.player-name-holder-active').removeClass('player-name-holder-active');
        $(this).find('.player-score-holder-active').removeClass('player-score-holder-active');
    });
    allPlayersLg.each(function () {
        $(this).find('.player-photo-holder-lg-active').removeClass('player-photo-holder-lg-active');
        $(this).find('.player-name-holder-lg-active').removeClass('player-name-holder-lg-active');
        $(this).find('.player-score-holder-lg-active').removeClass('player-score-holder-lg-active');
        $(this).find('.player-photo-holder-lg-active').removeClass('player-photo-holder-lg-active');
    });

    if (!end) {
        currentOtherPlayer.find('.player-name-holder').addClass('player-name-holder-active');
        currentOtherPlayer.find('.player-score-holder').addClass('player-score-holder-active');

        currentOtherPlayerLg.find('.player-photo-holder-lg').addClass('player-photo-holder-lg-active');
        currentOtherPlayerLg.find('.player-name-holder-lg').addClass('player-name-holder-lg-active');
        currentOtherPlayerLg.find('.player-score-holder-lg').addClass('player-score-holder-lg-active');
    }

    if (msg.payload.data.currentPlayer === playerId) {
        if (!end) {
            $('#actions').addClass('actionbar-active');
            yourTurnSound.play().then(function () {
                currentPlayer = true;
            });
        }
    } else {
        $('#actions').removeClass('actionbar-active');
        disableOutButtons();
        currentPlayer = false;
    }
}

function hideActionBar() {
    $('#actions').removeClass('actionbar-active');
    $('#actions').removeClass('actionbar-out');
    $('#out-btn').addClass(['bg-opacity-0', 'text-opacity-0', 'border-gray-200']);
    $('#out-btn-lg').addClass(['bg-opacity-0', 'text-opacity-0', 'border-gray-200']);
    currentPlayer = false;
}

function updateScores(msg) {
    msg.payload.data.scores.forEach((player) => {
        console.log("CHECKING SCORE: " + player.id + " (" + player.score + ")");
        if (player.id !== playerId) {

            var smallScore = $('#' + player.id).find(".score");
            var lgScore = $('#' + player.id + "-lg").find(".score");

            if (smallScore.text() !== player.score) {
                let currentScore = parseInt(smallScore.text());
                let targetScore = player.score;
                let scoreAnimation = setInterval(function () {
                    if (currentScore < targetScore) {
                        currentScore++;
                        smallScore.text(currentScore);
                    } else {
                        clearInterval(scoreAnimation);
                    }
                }, 100);

                let currentScoreLg = parseInt(lgScore.text());
                let scoreAnimationLg = setInterval(function () {
                    if (currentScoreLg < targetScore) {
                        currentScoreLg++;
                        lgScore.text(currentScoreLg);
                    } else {
                        clearInterval(scoreAnimationLg);
                    }
                }, 100);
            }
        } else {
            var myscore = $('#score').text();
            if (myscore !== player.score) {
                var mySmallScore = $('#score');
                var myLargeScore = $('#score-lg');

                let myCurrentScore = parseInt(mySmallScore.text());
                let myScoreAnimation = setInterval(function () {
                    if (myCurrentScore < player.score) {
                        myCurrentScore++;
                        mySmallScore.text(myCurrentScore);
                        myLargeScore.text(myCurrentScore);
                    } else {
                        clearInterval(myScoreAnimation);
                    }
                }, 100);
            }
        }
    });
}

export function displayOutPlayer(msg, invert) {
    if (msg.payload.data === playerId) {
        $('#actions').addClass('actionbar-out');
    } else {
        var currentPlayer = $('#' + msg.payload.data);
        var currentPlayerLg = $('#' + msg.payload.data + "-lg");

        currentPlayer.find('.player-name-holder').addClass('player-name-holder-out');
        currentPlayer.find('.player-score-holder').addClass('player-score-holder-out');

        currentPlayerLg.find('.player-name-holder-lg').addClass('player-name-holder-lg-out');
        currentPlayerLg.find('.player-score-holder-lg').addClass('player-score-holder-lg-out');
        currentPlayerLg.find('.player-photo-holder-lg').addClass('player-photo-holder-lg-out');
    }
}

export function hideOutPlayers() {
    $('.player-name-holder-out').removeClass('player-name-holder-out');
    $('.player-score-holder-out').removeClass('player-score-holder-out');

    $('.player-name-holder-lg-out').removeClass('player-name-holder-lg-out');
    $('.player-score-holder-lg-out').removeClass('player-score-holder-lg-out');
    $('.player-photo-holder-lg-out').removeClass('player-photo-holder-lg-out');
}

function setInfo(msg) {
    var roundNo;
    switch (msg.payload.data.roundNumber) {
        case 13:
            roundNo = "Kings";
            break;
        case 12:
            roundNo = "Queens";
            break;
        case 11:
            roundNo = "Jacks";
            break;
        default:
            roundNo = msg.payload.data.roundNumber + "'s";
    }


    $('#round').text(roundNo);
    $('#round-lg').text(roundNo);
    $('#score').text(msg.payload.data.player.score);
    $('#score-lg').text(msg.payload.data.player.score);
}

export function handleActionMessage(msg) {
    switch (msg.payload.type) {
        case "drawdiscard":
            var discardTopElId = "#card" + discardTop;
            $(discardTopElId).remove();
            $('#discard-placeholder').show();
        case "draw2":
        case "draw1":
            if (msg.payload.data.currentPlayer === playerId) {
                drawn = true
                for (const card of msg.payload.data.player.hand) {
                    if (!$('#card' + card.id).length) {
                        addCardToHand(card.id, card.suit, card.value);
                        if ($('#discard-placeholder').is(":visible")) {
                            $('#discard-placeholder').droppable({
                                tolerance: "intersect",
                                drop: function (event, ui) {
                                    if ($('#book-builder').is(":visible")){
                                        var card = ui.draggable;
                                        card.detach();
                                        card.css('position', 'static');
                                        card.css('top', 0);
                                        card.css('left', 0);
                                        $('#hand').append(card);
                                        return;
                                    }
                                    var cardId = ui.draggable.prop('id').replace('card', '');

                                    tempDiscard = ui.draggable;
                                    tempDiscard.detach();
                                    tempDiscard.css('top', 0);
                                    tempDiscard.css('left', 0);
                                    tempDiscard.css('position', 'static');

                                    if (lastRound) {
                                        if ($('#hand').children().length < 1) {
                                            var outMessage = {
                                                type: 'intent',
                                                payload: {
                                                    type: 'out',
                                                    data: {
                                                        player: playerId,
                                                        discard: cardId,
                                                        books: books
                                                    }
                                                }
                                            };
                                            conn.send(JSON.stringify(outMessage));
                                        } else {
                                            var remainder = [];
                                            $('#hand').children().each(function () {
                                                remainder.push($(this).prop('id').replace("card", ""));
                                            });
                                            var layMessage = {
                                                type: 'intent',
                                                payload: {
                                                    type: 'lay',
                                                    data: {
                                                        player: playerId,
                                                        discard: cardId,
                                                        books: books,
                                                        remainder: remainder
                                                    }
                                                }
                                            };
                                            conn.send(JSON.stringify(layMessage));
                                        }
                                    } else {
                                        if (books.length > 0 && $('#hand').children().length < 1) {
                                            var outFirstMessage = {
                                                type: 'intent',
                                                payload: {
                                                    type: 'out',
                                                    data: {
                                                        player: playerId,
                                                        discard: cardId,
                                                        books: books
                                                    }
                                                }
                                            };
                                            conn.send(JSON.stringify(outFirstMessage));
                                        } else {
                                            var message = {
                                                type: 'intent',
                                                payload: {
                                                    type: 'discard',
                                                    data: {
                                                        player: playerId,
                                                        cardId: cardId
                                                    }
                                                }
                                            }
                                            conn.send(JSON.stringify(message));
                                        }
                                    }
                                }
                            });
                        } else {
                            $('#card' + discardTop).droppable({
                                tolerance: "intersect",
                                drop: function (event, ui) {
                                    if ($('#book-builder').is(":visible")){
                                        var card = ui.draggable;
                                        card.detach();
                                        card.css('position', 'static');
                                        card.css('top', 0);
                                        card.css('left', 0);
                                        $('#hand').append(card);
                                        return;
                                    }
                                    var cardId = ui.draggable.prop('id').replace('card', '');
                                    $('#card' + discardTop).remove();
                                    ui.draggable.remove();
                                    $('#discard-placeholder').show();
                                    if (lastRound) {
                                        if ($('#hand').children().length < 1) {
                                            var outMessage = {
                                                type: 'intent',
                                                payload: {
                                                    type: 'out',
                                                    data: {
                                                        player: playerId,
                                                        discard: cardId,
                                                        books: books
                                                    }
                                                }
                                            };
                                            conn.send(JSON.stringify(outMessage));
                                        } else {
                                            var remainder = [];
                                            $('#hand').children().each(function () {
                                                remainder.push($(this).prop('id').replace("card", ""));
                                            });
                                            var layMessage = {
                                                type: 'intent',
                                                payload: {
                                                    type: 'lay',
                                                    data: {
                                                        player: playerId,
                                                        discard: cardId,
                                                        books: books,
                                                        remainder: remainder
                                                    }
                                                }
                                            };
                                            conn.send(JSON.stringify(layMessage));
                                        }
                                    } else {
                                        if (books.length > 0 && $('#hand').children().length < 1) {
                                            var outFirstMessage = {
                                                type: 'intent',
                                                payload: {
                                                    type: 'out',
                                                    data: {
                                                        player: playerId,
                                                        discard: cardId,
                                                        books: books
                                                    }
                                                }
                                            };
                                            conn.send(JSON.stringify(outFirstMessage));
                                        } else {
                                            var message = {
                                                type: 'intent',
                                                payload: {
                                                    type: 'discard',
                                                    data: {
                                                        player: playerId,
                                                        cardId: cardId
                                                    }
                                                }
                                            }
                                            conn.send(JSON.stringify(message));
                                        }
                                    }
                                }
                            });
                        }
                    }
                }
                console.log("here");
                enableOutButtons();
                if (lastRound) {
                    showBookBuilder(true);
                }
            } else {
                if (msg.payload.type !== "drawdiscard") {
                    $('#' + msg.payload.type).hide();
                    setTimeout(function () {
                        $('#' + msg.payload.type).show();
                    }, 500);
                }
                var drawAck = {
                    type: 'ack',
                    payload: {
                        type: 'draw'
                    }
                };
                playRandomDraw();
                console.log(drawAck);
                conn.send(JSON.stringify(drawAck));
            }
            break;
        case "discard":
            disableOutButtons();
            tempDiscard = {};
            var discardAck = {
                type: 'ack',
                payload: {
                    type: 'discard'
                }
            };
            conn.send(JSON.stringify(discardAck));
            changeDiscard(msg.payload.data.discardPileTop.id, msg.payload.data.discardPileTop.suit, msg.payload.data.discardPileTop.value);
            break;
        case "out":
            displayOutPlayer(msg, false);
            if (!lastRound) {
                outSound.play();
                lastRound = true;
            } else {
                outSoundNotFirst.play();
            }
            var outAck = {
                type: 'ack',
                payload: {
                    type: 'out'
                }
            };
            conn.send(JSON.stringify(outAck));
            break;
        case "lay":
            var layAck = {
                type: 'ack',
                payload: {
                    type: 'lay'
                }
            };
            conn.send(JSON.stringify(layAck));
            break;
    }
}

export function handleInfoMessage(msg) {
    switch (msg.payload.type) {
        case "welcome":
            if (playerName !== ""){
                startGame();
            }
            else {
                $('#start-panel').show();
            }
            break;
        case "registration":
            playerId = msg.payload.data;
            console.log("playerId: " + playerId);
            var regAck = {
                type: 'ack',
                payload: {
                    type: 'registration',
                }
            };
            conn.send(JSON.stringify(regAck));
            break;
        case "player":
            if (msg.payload.data.id !== playerId) {
                addPlayer(msg.payload.data.id, msg.payload.data.name, msg.payload.data.gender, msg.payload.data.score);
            }
            break;
        case "badout":
            resetGoOutCards();
            tempDiscard.css('position', '')
            $('#hand').append(tempDiscard);
            break;
    }
}

export function handleStateMessage(msg) {
    switch (msg.payload.type) {
        case "init":
            lastRound = false;
            books = [];
            setInfo(msg);
            hideOutPlayers();
            hideActionBar();
            updateScores(msg);
            $('#out-btn, #out-btn-lg').prop('disabled', true);
            discardTop = msg.payload.data.discardPileTop.id;
            roundStartSound.play().then(function () {
                dealCards(msg);
            });
            break;
        case "begin":
            displayCurrentPlayer(msg);
            var beginAck = {
                type: 'ack',
                payload: {
                    type: 'begin'
                }
            };
            conn.send(JSON.stringify(beginAck));
            break;
        case "update":
            displayCurrentPlayer(msg);
            drawn = false;
            discardTop = msg.payload.data.discardPileTop.id;
            var updateAck = {
                type: 'ack',
                payload: {
                    type: 'update'
                }
            };
            conn.send(JSON.stringify(updateAck));
            break;
        case "endr":
            displayCurrentPlayer(msg, true);
            updateScores(msg);
            let playingCards = $('.playingcard');
            if (playingCards.length) {
                playingCards.each(function () {
                    $(this).remove();
                });
            }
            roundEndSound.play();
            var endrAck = {
                type: "ack",
                payload: {
                    type: 'endr'
                }
            };
            conn.send(JSON.stringify(endrAck));
            break;
        case "endg":
            createScoreboard(msg);
            conn.close();
            break;
    }
}