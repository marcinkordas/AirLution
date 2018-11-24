using System;
using System.Collections.Generic;
using System.Diagnostics;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using MQTTnet;
using MQTTnet.Server;


namespace TestMQTTBroker
{
    /// <summary>
    /// Implementation of MQQT Broker based on MQQTnet library
    /// </summary>
    class MQTTBroker
    {
        private static Firebase_IoT_Manger.DatabaseManager<double> databaseManager;

        /// <summary>
        /// Init of MQQT Broker
        /// </summary>
        public static async void StartBroker()
        {
            try
            {
                databaseManager = new Firebase_IoT_Manger.DatabaseManager<double>();

                var optionsBuilder = new MqttServerOptionsBuilder()
                    .WithConnectionBacklog(100)
                    .WithDefaultEndpointPort(1883);

                // Start a MQTT server.
                var mqttServer = new MqttFactory().CreateMqttServer();

                mqttServer.ClientConnected += MqttServer_ClientConnected;
                mqttServer.ApplicationMessageReceived += MqttServer_ApplicationMessageReceived;

                await mqttServer.StartAsync(optionsBuilder.Build());
                Console.WriteLine("MQTT Server started.");
                //await mqttServer.StopAsync();
            }
            catch (Exception ex)
            {
                Console.WriteLine(ex.Message);
            }
        }

        /// <summary>
        /// Event handler for incoming message
        /// </summary>
        /// <param name="sender"></param>
        /// <param name="e"></param>
        private static void MqttServer_ApplicationMessageReceived(object sender, MqttApplicationMessageReceivedEventArgs e)
        {
            string _topic = e.ApplicationMessage.Topic.ToString();
            string _payload = e.ApplicationMessage.ConvertPayloadToString();

            Console.WriteLine("Messsage Topic = " + e.ApplicationMessage.Topic.ToString());
            Console.WriteLine("Message Payload = " + e.ApplicationMessage.ConvertPayloadToString());

            //zaimplementuj sychronizacje czasu

            databaseManager.pushAsync("id", 0, 0, 0, 0);



        }

        /// <summary>
        /// Event handler for new client connection
        /// </summary>
        /// <param name="sender"></param>
        /// <param name="e"></param>
        private static void MqttServer_ClientConnected(object sender, MqttClientConnectedEventArgs e)
        {
            Console.WriteLine("Client connected, Id = " + e.ClientId);
        }


        /// <summary>
        /// Converting string paylod to double
        /// </summary>
        /// <param name="payload"></param>
        /// <returns></returns>
        private static double[] ConvertPayloadToDouble(string payload)
        {
            ///to do - convert payload to double array


            double[] retValue = { 1 };
            return retValue;
        }
    }
}
