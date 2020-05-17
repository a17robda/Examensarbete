// Import SparkSession
import org.apache.spark.sql.SparkSession

object SparkApp {
    def main(args: Array[String]) {
        val spark = SparkSession.builder.appName("Spark Application").getOrCreate()

        // Args
        var testType : String = args(0)
        var iter : Int = args(1).toInt
        var dbSize : String = args(2);

        // Import primitive datatypes
        import spark.implicits._
        import scala.io.Source

        var maxId : Int = 0;
        val filename = "/home/robin/Documents/Examensarbete/generate_datasets/outputJSON" + dbSize + "/max/maxId.txt"
        for (line <- Source.fromFile(filename).getLines) {
            maxId = line.toInt
        }

        // Create a query string
        val queryString = queryBuilder(testType, maxId, iter)
        // Load JSON-file in a Data Frame
        val jsonPath = "/home/robin/Documents/Examensarbete/generate_datasets/outputJSON" + dbSize
        val jsonDF = spark.read
        .option("multiLine", true).option("mode", "PERMISSIVE")
        .json(jsonPath)

        jsonDF.printSchema()
        // Create a new view
        jsonDF.createOrReplaceTempView("values")
        // Execute the query
        val selectAll = spark.sql(queryString)
        selectAll.show()
        // Write to JSON
        selectAll.write.mode("overwrite").format("json").save("/home/robin/Documents/Examensarbete/spark/sparkOut.json")

        // STOPS THE CURRENT SPARK SESSION
        spark.stop()
    }

    def queryBuilder(choice:String, maxId:Int, iter:Int) : String = {

        val maxRand = randomize(maxId, iter)
        println("Max rand: " + maxRand)
        choice match {
            case "0" =>
                return "SELECT * FROM values WHERE tkeycode = " + maxRand
            case "1" =>
                return "SELECT MAX(tvalue) AS maximum, MIN(tvalue) AS minimum, AVG(tvalue) AS average, SUM(tvalue) AS sum_energy, STD(tvalue) AS std_dev, VARIANCE(tvalue) AS variance FROM values WHERE tstamp BETWEEN '1976-12-31%' AND " + "'" + randomizeTimestamp(maxRand) + "%'"
        }
    }

    // Randomize a timestamp according to seed
    def randomizeTimestamp(seed:Int) : String = {
        val rand = new scala.util.Random(seed)

        var year : Int = rand.nextInt(2020 - 1977) + 1977
        var month : Int = rand.nextInt((12 - 1) + 1)
        var day : Int = rand.nextInt((31 - 1) + 1)

        // String representation
        var stringYear : String = year.toString
        var stringMonth : String = month.toString
        var stringDay : String = day.toString

        if(month < 10) {
            stringMonth = "0" + stringMonth
        }
        if(day < 10) {
            stringDay = "0" + stringDay
        }
        return stringYear + "-" + stringMonth + "-" + stringDay
    }

    def randomize(maxId:Int, iter:Int) : Int = {
        // Creates a new random and seeds it according to iteration
        val rand = new scala.util.Random(iter)
        val randInt = rand.nextInt(maxId)
        return randInt
    }
}