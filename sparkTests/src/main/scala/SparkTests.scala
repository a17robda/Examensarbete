// Import SparkSession
import org.apache.spark.sql.SparkSession

object SparkTests {
    def main(args: Array[String]) {
        val spark = SparkSession.builder.appName("Spark Tests").getOrCreate()

        var testType : String = args(0)
        var iter : Int = args(1).toInt

        // Import primitive datatypes
        import spark.implicits._
        import scala.io.Source

        var maxId : Int = 0;
        val filename = "/home/robin/Documents/Examensarbete/generate_datasets/outputJSON/max/maxId.txt"
        for (line <- Source.fromFile(filename).getLines) {
            maxId = line.toInt
        }

        val queryString = queryBuilder(testType, maxId, iter)
        val jsonPath = "/home/robin/Documents/Examensarbete/generate_datasets/outputJSON"
        val jsonDF = spark.read
        .option("multiLine", true).option("mode", "PERMISSIVE")
        .json(jsonPath)

        jsonDF.printSchema()

        jsonDF.createOrReplaceTempView("values")

        val selectAll = spark.sql(queryString)
        selectAll.show()

        selectAll.write.mode("overwrite").format("json").save("/home/robin/Documents/Examensarbete/sparkTests/sparkOut.json")

        // STOPS THE CURRENT SPARK SESSION
        spark.stop()
    }

    def queryBuilder(choice:String, maxId:Int, iter:Int) : String = {

        val maxRand = randomize(maxId, iter)
        println("Max rand: " + maxRand)
        choice match {
            case "0" =>
                return "SELECT * FROM values WHERE nyckelkod == " + maxRand
            case "1" =>
                return "SELECT avg(tvalue) AS avg, std(tvalue) as std FROM values WHERE nyckelkod < " + maxRand
        }
}
    def randomize(maxId:Int, iter:Int) : Int = {
        // Creates a new random and seeds it according to iteration
        val rand = new scala.util.Random(iter)
        val randInt = rand.nextInt(maxId)
        return randInt
    }
}