import { createServer } from "node:http";
import { Server } from "socket.io";
import { createAdapter } from "@socket.io/redis-adapter";
import Redis from "ioredis";

const port = Number(process.env.WS_PORT || 4001);
const redisHost = process.env.REDIS_HOST || "127.0.0.1";
const redisPort = Number(process.env.REDIS_PORT || 6379);

const httpServer = createServer();
const io = new Server(httpServer, {
  cors: {
    origin: "*",
  },
});

const pubClient = new Redis({ host: redisHost, port: redisPort, maxRetriesPerRequest: null });
const subClient = pubClient.duplicate();
const emitConnectionWarning = (source) => (error) => {
  console.warn(`[Redis:${source}] ${error.message}`);
};
pubClient.on("error", emitConnectionWarning("pub"));
subClient.on("error", emitConnectionWarning("sub"));

let redisAdapterReady = false;
Promise.all([pubClient.connect(), subClient.connect()])
  .then(() => {
    io.adapter(createAdapter(pubClient, subClient));
    redisAdapterReady = true;
    console.log("Redis adapter connected");
  })
  .catch((error) => {
    console.warn(`Redis adapter disabled: ${error.message}`);
  });

io.on("connection", (socket) => {
  socket.emit("dashboard:activity", "Realtime stream connected");
});

setInterval(() => {
  io.emit(
    "dashboard:activity",
    `Live update ${new Date().toLocaleTimeString()}${redisAdapterReady ? " • redis" : " • local"}`
  );
}, 12000);

httpServer.listen(port, () => {
  console.log(`PHN websocket server running on :${port}`);
});
