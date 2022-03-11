This application uses PHP 8 e Kafka to translate IPs into geographical locations using the [IPStack free API](https://ipstack.com/documentation).

<p align="center">
  <img alt="Topology" src="https://user-images.githubusercontent.com/943036/148793496-5f73bd8f-f515-4e28-8fa6-9fbc88aa0ca4.png" />
</p>

##### Application lifecycle:
* A PHP process consumes a Kafka Topic entry
* Incoming messages are sent to IPStack
* A new message is created using the returned IP geolocation data
* The new message is sent to a Kafka Topic output
* The request per client + IP is cached in Redis for 30 minutes.


### How to set up

1. [Get free API Access Key](https://ipstack.com/signup/free) and put the value in .env variable `IPSTACK_ACCESS_KEY`
2. Run docker-compose up -d
3. done.

### How to test

1. Start consume the output kafka topic running `./bin/consume-output-topic.sh`
2. Open a Kafka Producer running `./bin/open-input-producer.sh` and send a new message:<br />
   Input message sample:</p>
   ```json  
   {"ip": "2001:1284:f013:2c2c:13d9:135d:aff9:c52d", "clientId": 1}
4. A message with the ip geolocation will appear in the consumer window.<br/>
   Output message example<br/>
   ```json 
   {
     "ip": "2001:1284:f013:2c2c:13d9:135d:aff9:c52d",
     "timestamp": 1647020864,
     "clientId": 1,
     "latitude": -25.427776336669922,
     "longitude": -49.27305221557617,
     "country": "Brazil",
     "region": "Parana",
     "city": "Curitiba"
   }
### Run unit tests
`./bin/unit-tests.sh`
