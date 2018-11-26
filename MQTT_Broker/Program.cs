using System;
using MQTTnet;
using MQTTnet.Server;
using MQTTnet.Diagnostics;
using System.Numerics;
using System.Collections.Generic;

namespace MqttBroker
{
    class Program
    {
        static FirebaseManager.DatabaseManager<double> databaseManager;
        static void Main(string[] args)
        {
            databaseManager = new FirebaseManager.DatabaseManager<double>();

            StartBroker();
            Console.ReadLine();
        }

        public static async void StartBroker()
        {
            try
            {
                var optionsBuilder = new MqttServerOptionsBuilder()
                    .WithConnectionBacklog(100)
                    .WithDefaultEndpointPort(1883);
                    //.WithDefaultEndpointBoundIPAddress(System.Net.IPAddress.Parse("192.168.0.11"));

                // Start a MQTT server.
                var mqttServer = new MqttFactory().CreateMqttServer();
				// Write all trace messages to the console window.
				MqttNetGlobalLogger.LogMessagePublished += (s, e) =>
				{
					var trace = $">> [{e.TraceMessage.Timestamp:O}] [{e.TraceMessage.ThreadId}] [{e.TraceMessage.Source}] [{e.TraceMessage.Level}]: {e.TraceMessage.Message}";
					if (e.TraceMessage.Exception != null)
					{
						trace += Environment.NewLine + e.TraceMessage.Exception.ToString();
					}

					Console.WriteLine(trace);
				};
				
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

        private static void MqttServer_ApplicationMessageReceived(object sender, MqttApplicationMessageReceivedEventArgs e)
        {
            string topic = e.ApplicationMessage.Topic.ToString();
            string payload = e.ApplicationMessage.ConvertPayloadToString();

            Console.WriteLine("Messsage Topic = " + topic);
            Console.WriteLine("Message Payload = " + payload);

            List<double> splitedPayload = new List<double>();
            if (ConvertPayloadtoDouble(payload, ref splitedPayload))
            {
                databaseManager.PushAsync(topic, DateTime.Now.ToString(), splitedPayload[0], splitedPayload[1], splitedPayload[2], splitedPayload[3]);
            }

        }

        private static void MqttServer_ClientConnected(object sender, MqttClientConnectedEventArgs e)
        {
            Console.WriteLine("Client connected, Id = " + e.ClientId);
        }


        private static bool ConvertPayloadtoDouble(string payload, ref List<double> splitedPayload)
        {
            try
            {
                string[] split = payload.Split(new char[0], StringSplitOptions.RemoveEmptyEntries);
                double tempNumber;
                foreach (var data in split)
                {
                    if (Double.TryParse(data, out tempNumber))
                    {
                        splitedPayload.Add(tempNumber);
                    }
                    else
                    {
                        Console.WriteLine("Unable to parse '{0}'.", data);
                        break;
                    }
                }
                if (splitedPayload.Count != 4)
                {
                    throw new Exception("Wrong num of arguments");             
                }
                else
                {
                    return true;
                }
            }
            catch (Exception ex)
            {
                throw ex;
            }
        }
    }
}