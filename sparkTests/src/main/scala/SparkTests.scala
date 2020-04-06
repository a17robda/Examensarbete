// Import SparkSession
import org.apache.spark.sql.SparkSession

object SparkTests {
    def main(args: Array[String]) {
        val spark = SparkSession.builder.appName("Spark Tests").getOrCreate()

        // Import primitive datatypes
        import spark.implicits._

        println("Hello world!");

        val jsonPath = "/home/robin/Documents/Examensarbete/generate_datasets/outputJSON"
        val jsonDF = spark.read
        .option("multiLine", true).option("mode", "PERMISSIVE")
        .json(jsonPath)

        jsonDF.printSchema()

        jsonDF.createOrReplaceTempView("values")

        val selectAll = spark.sql("SELECT * FROM values WHERE nyckelkod < 5000000")
        selectAll.show()

        spark.stop()
    }
}