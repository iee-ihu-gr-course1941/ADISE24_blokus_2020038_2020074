# ADISE24_blokus_2020038_2020074
Blokus implementation
# Game API Documentation

## **User Endpoints**

### **User Info**
`GET /user/info`
Retrieves information about the authenticated user.

---

### **Create User**
`POST /user/create/${username}/${password}`
Creates a new user with the given username and password.

---

### **Scoreboard**
`GET /user/scoreboard`
Returns the leaderboard.

---

## **Game Endpoints**

### **Game State**
`GET /game/state/${gameid}`
Retrieves the current state of the game board

---

### **Current Turn**
`GET /game/turn/${gameid}`
Returns the current turn number of the game.

---

### **Players in Game**
`GET /game/players/${gameid}`
Lists all players in the game.

---

### **Available piece arrays**
`GET /game/pieces/${gameid}`
Lists the 2D arrays of available pieces

---

### **Available piece IDs**
`GET /game/pieceids/${gameid}`
Lists the IDs of available pieces.

---

### **Current color & position**
`GET /game/position/${gameid}`
Returns color & position

---

### **Update activity**
`GET /game/update_activity/${gameid}`
Updates activity timestamp

---

### **Place a Piece**
`POST /game/place/${gameid}/${pieceid}/${x}/${y}/${r}`
Places a piece on the board.

---

### **Game Scores**
`GET /game/scores/${gameid}`
Retrieves the scores of the players in the game.

---

## **Room Endpoints**

### **Join Room**
`POST /rooms/join/${roomid}/${password?}`
Joins a room (password optional).

---

### **Create Room**
`POST /rooms/create/${password?}`
Creates a new room (password optional).

---

### **Room Info**
`GET /rooms/info`
Lists all available rooms.

