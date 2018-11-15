using System;
using System.Collections.Generic;
using System.Diagnostics;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using MQTTnet;
using MQTTnet.Server;


namespace EiT_SiU_SensorManager
{
    class MQTTBroker
    {
        public static async void StartBroker()
        {
            try
            {
                var optionsBuilder = new MqttServerOptionsBuilder()
                    .WithConnectionBacklog(100)
                    .WithDefaultEndpointPort(1883);

                // Start a MQTT server.
                var mqttServer = new MqttFactory().CreateMqttServer();

                mqttServer.ClientConnected += MqttServer_ClientConnected;
                mqttServer.ApplicationMessageReceived += MqttServer_ApplicationMessageReceived;

                await mqttServer.StartAsync(optionsBuilder.Build());
                Debug.WriteLine("MQTT Server started.");
                await mqttServer.StopAsync();
            }
            catch (Exception ex)
            {
                Debug.WriteLine(ex.Message);
            }
        }

        private static void MqttServer_ApplicationMessageReceived(object sender, MqttApplicationMessageReceivedEventArgs e)
        {
            string _topic = e.ApplicationMessage.Topic.ToString();
            string _payload = e.ApplicationMessage.ConvertPayloadToString();

            Debug.WriteLine("Messsage Topic = " + e.ApplicationMessage.Topic.ToString());
            Debug.WriteLine("Message Payload = " + e.ApplicationMessage.ConvertPayloadToString());



        }

        private static void MqttServer_ClientConnected(object sender, MqttClientConnectedEventArgs e)
        {
            Debug.WriteLine("Client connected, Id = " + e.ClientId);
        }

        private static double[] ConvertPayloadToDouble(string payload)
        {
            ///to do - convert payload to double array


            double[] retValue = { 1 };
            return retValue;
        }
    }
}
