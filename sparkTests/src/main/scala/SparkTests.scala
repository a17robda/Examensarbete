// Import SparkSession
import org.apache.spark.sql.SparkSession

object SparkTests {
    def main(args: Array[String]) {
        val spark = SparkSession.builder.appName("Spark Tests").getOrCreate()

        // Import primitive datatypes
        import spark.implicits._

        println("Hello world!");

        val jsonPath = "/home/me/Documents/Examensarbete/generate_datasets/outputJSON"
        val jsonDF = spark.read.json(jsonPath)
        jsonDF.printSchema()

        jsonDF.createOrReplaceTempView("values")

        val selectAll = spark.sql("SELECT * FROM values")
        selectAll.show()

        spark.stop()
    }
}