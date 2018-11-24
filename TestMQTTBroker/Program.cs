using System;

namespace TestMQTTBroker
{
    class Program
    {
        static void Main(string[] args)
        {
            Console.WriteLine("MQTT Broker is gong to start");
            MQTTBroker.StartBroker();


            Console.ReadLine();
        }
    }
}
