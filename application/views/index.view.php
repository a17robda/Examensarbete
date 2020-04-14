<?php

initialize();

function initialize() {
    buildFront();
}

function buildFront() {
    echo '
    <h1>Energy Data Aggregation</h1><br>
    <form id="aggForm" action="/query" method="POST">
    <select id="queryChoice" name="queryChoice">
    <option value="simple">Simple Query</option>
    <option value="complex">More Complex Query</option>
    <option value="batch">Batch Query</option>
    </select><br>
    <label for="iteration">Iteration:</label>
    <input type="text" id="iteration" name="iteration"><br>
    <input type="radio" id="mysql" name="aggregation" value="mysql">
    <label for="mysql">MySQL</label><br>
    <input type="radio" id="spark" name="aggregation" value="spark">
    <label for="spark">Apache Spark</label><br>
    <label for "">Data size:</label><br>
    <input type="radio" id="one" name="dataSize" value="one">
    <label for="one">1 GB</label><br>
    <input type="radio" id="ten" name="dataSize" value="ten">
    <label for="ten">10 GB</label><br>
    <input type="radio" id="fifty" name="dataSize" value="fifty">
    <label for="fifty">50 GB</label><br>
    <input id="submit" type="submit">
    </form>
';
}

?>