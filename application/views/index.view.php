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
    </select><br>
    <label for="iteration">Iteration:</label>
    <input type="text" id="iteration" name="iteration"><br>
    <input type="radio" id="mysql" name="aggregation" value="mysql">
    <label for="mysql">MySQL</label><br>
    <input type="radio" id="spark" name="aggregation" value="spark">
    <label for="spark">Apache Spark</label><br>
    <label>Data size:</label><br>
    <input type="radio" id="one" name="dataSize" value="one">
    <label for="one">1 GB</label><br>
    <input type="radio" id="five" name="dataSize" value="five">
    <label for="ten">5 GB</label><br>
    <input type="radio" id="thirteen" name="dataSize" value="thirteen">
    <label for="fifty">13 GB</label><br>
    <input id="submit" type="submit">
    </form>
';
}

?>