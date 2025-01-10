# ADISE24_blokus_2020038_2020074
Blokus implementation by Michail Konstantinos Dimopoulos & Konstantinos Koufoudakis.
[Live Demo](https://users.iee.ihu.gr/~iee2020038/login.html)

## features
* Multiple players & games at the same time
* Score counting & leaderboard
* Fully-featured graphical UI
* Inactivity detection & kicks
* In-house user account management
* No plaintext password storage

There are some known issues that were not fixed due to time contraints. You'll find them in the issues tab.

## Note
Although the files are stored in the api directory, the front end files use API endpoints in a directory named lamp, as that is the name used in the live demo.

# Game API Documentation

A thin-API approached was followed to facilitate front-end development. This has led to some redundancy, but it has been minimized.

## **User Endpoints**

`/user/info`
Retrieves information about the authenticated user.

---

`/user/create/${username}/${password}`
Creates a new user with the given username and password.

---

`/user/scoreboard`
Returns the leaderboard.

---

## **Game Endpoints**

`/game/state/${gameid}`
Retrieves the current state of the game board

---

`/game/turn/${gameid}`
Returns the current turn number of the game.

---

`/game/players/${gameid}`
Lists all players in the game.

---

`/game/pieces/${gameid}`
Lists the 2D arrays of available pieces

---

`/game/pieceids/${gameid}`
Lists the IDs of available pieces.

---

`/game/position/${gameid}`
Returns color & position

---

`/game/update_activity/${gameid}`
Updates activity timestamp

---

`/game/place/${gameid}/${pieceid}/${x}/${y}/${r}`
Places a piece on the board.

---

`/game/scores/${gameid}`
Retrieves the scores of the players in the game.

---

## **Room Endpoints**

`/rooms/join/${roomid}/${password?}`
Joins a room (password optional).

---

`/rooms/create/${password?}`
Creates a new room (password optional).

---

`/rooms/info`
Lists all available rooms.


