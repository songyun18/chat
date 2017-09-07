var host='0.0.0.0';
var port=8080;

var http = require('http');
var server=http.createServer();

var io=require('socket.io')(server);

var users={};
var rooms={};
var roomInfo={};

io.on('connection',function(socket)
{
	var socketId=socket.id;
	console.log(socketId+'连接');
	
	socket.on('join',function(data)
	{
		console.log(data.user_id+'加入房间');
		//加入房间
		var roomName='room_'+data['chat_id'];
		socket.join(roomName);
	});
	
	socket.on('leave',function(data)
	{
		console.log(data.user_id+'离开房间');
		//离开房间
		var roomName='room_'+data['chat_id'];
		socket.leave(roomName);
	});
	
	socket.on('message',function(data)
	{
		console.log('发送消息');
		var roomName='room_'+data['chat_id'];
		socket.to(roomName).emit('message',data.message);
	});
	
	
	socket.on('disconnect',function()
	{
		console.log(socketId+'失去连接');
	});
});

server.listen(port,host);
console.log('Server listened on '+port);
