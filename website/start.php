<?php
/**
* This file fetches data from the database and visualizes by topological graph and table.
* Can be initialized by pressing "Start" button in the homepage or typing "/start.php" URL postfix.
*
* @author 	IT60070011, IT60070096, IT60070102
* @version	0.9.2
* @since  	0.2.0
*/
?>
<!DOCTYPE html>
<html lang="th">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<title>Start</title>
	<link rel="stylesheet" href="assets/css/main.css">
	<link rel="stylesheet" href="assets/css/font-awesome.min.css">
	<link rel="stylesheet" href="assets/css/vis.min.css">
</head>
<body>
<main class="penTestingUI">
    <section id="clock">00:00:00</section>
    <section id="reset" class="buttonContainer"><a onclick="resetDB()"><i class="fa fa-repeat" aria-hidden="true"></i> ล้างข้อมูล</a></section>
    <section id="readyOverlay"><h2><i class="fa fa-check-circle" aria-hidden="true"></i> พร้อมแล้ว กรุณาเริ่มยิงคำสั่ง</h2></section>
	<section id="topology" class="topology"></section>
	<section class="resultTable">
		<table>
			<thead>
				<tr>
					<th>เวลา</th>
					<th>รายละเอียด</th>
					<th>Device IP</th>
					<th>Port</th>
					<th>OS</th>
					<th>Architecture</th>
					<th>Parent IP</th>
				</tr>
			</thead>
			<tbody id="resultTableBody">
			</tbody>
		</table>
	</section>
</main>
<script src="assets/js/jquery-3.4.1.min.js"></script>
<script src="assets/js/vis.min.js"></script>
<script src="assets/js/clock.js"></script>
<script>
    const resetDB = () => {
        let confirmation = confirm("คุณกำลังจะลบทุกอย่างในฐานข้อมูลแล้วเริ่มระบบใหม่ และไม่สามารถย้อนกลับมาได้อีก ต้องการดำเนินการจริง ๆ หรือไม่");
        if (confirmation) {
            window.location = "reset.php"
        }
    };

    const imgGenerator = (os, state) => {
        let imgURL = "";
        os = os.toLowerCase();
        if (os.includes("windows")) {
            imgURL += "windows"
        } else if (os.includes("mac")) {
            imgURL += "mac"
        } else if (os.includes("ios")) {
            imgURL += "ios"
        } else {
            imgURL += "linux"
        }
        switch (state) {
            case "success":
                imgURL += "-su.png";
                break;
            case "fail":
                imgURL += "-fa.png";
                break;
            default:
                imgURL += "-ex.png";
        }

        return imgURL;
    };

    const descriptor = (state) => {
        switch (state) {
            case "attacker":
                return "เครื่องมือพร้อมทำงาน";
            case "standby":
                return "เครื่องมือกำลังตรวจหาอุปกรณ์ภายในเครือข่าย";
            case "scanning":
                return "เครื่องมือกำลังตรวจสอบช่องโหว่ภายในอุปกรณ์";
            case "exploit":
                return "เครือมือพบช่องโหว่ภายในระบบ";
            case "success":
                return "เครื่องมือเจาะเข้าช่องโหว่สำเร็จ";
            case "fail":
                return "เครื่องมือเจาะเข้าช่องโหว่ไม่สำเร็จ";
            default:
                return "ERR";
        }
    };

    const objectToArray = (obj) => {
        return Object.keys(obj).map(function (key) {
            obj[key].id = key;
            return obj[key];
        });
    };

    let network = false;
    let exportValue = "";

    const isSuccess = (ip, obj) => {
        let result = false;
        $.each(obj, function(key, val) {
            if (val['ip'] === ip && val['state'] === 'success') {
                result = true;
                return false;
            }
        });
        return result;
    };

    const isFail = (ip, obj) => {
        let result = false;
        $.each(obj, function(key, val) {
            if (val['ip'] === ip && val['state'] === 'fail')
            {
                result = true;
                return false;
            }
        });
        return result;
    };

    const fetchDB = () => {
        // create an array with nodes
        let nodesArray = new vis.DataSet();
        let edgesArray = [];

        $.ajax({
            url: "core/dbReader.php" ,
            type: "POST",
            data: '',
            success: function(result) {
                let obj = JSON.parse(result);
                if (obj.length > 0) {
                    $("#readyOverlay").css("display", "none");
                    if (network) {
                        exportValue = objectToArray(network.getPositions());
                    }
                    $("#resultTableBody").empty();
                    $.each(obj, function(key, val) {
                        // Fail State Sender
                        let tmpt = val["updated_time"].split(" ");
                        let objDate = tmpt[0].split("-");
                        let objTime = tmpt[1].split(":");

                        let timeNow = new Date();
                        let objTS = new Date(parseInt(objDate[0]), parseInt(objDate[1])-1, parseInt(objDate[2]), parseInt(objTime[0]), parseInt(objTime[1]), parseInt(objTime[2], 0));

                        if (timeNow - objTS > 30000 && val["state"] === "scanning" && !isSuccess(val['ip'], obj) && !isFail(val['ip'], obj)) {
                            val["state"] = "fail";
                            $.ajax({
                                url: "postAPI.php",
                                type: "POST",
                                data: JSON.stringify(val)
                            });
                            val["state"] = "scanning";
                        }

                        // Table Area
                        let tr = "<tr class='" + val["state"] + "'>";
                        tr = tr + "<td>" + val["updated_time"] + "</td>";
                        tr = tr + "<td>" + descriptor(val["state"]) + "</td>";
                        tr = tr + "<td>" + val["ip"] + "</td>";
                        tr = tr + "<td>" + val["port"] + "</td>";
                        tr = tr + "<td>" + val["os"] + "</td>";
                        tr = tr + "<td>" + val["arch"] + "</td>";
                        tr = tr + "<td>" + val["parent"] + "</td>";
                        tr = tr + "</tr>";
                        $('#resultTableBody:last').append(tr);

                        // Draw Area
                        let textLabel = "["+val["ip"]+"] "+val["os"]+" "+val["arch"]+" <"+val["state"]+">";
                        if (val["state"] === "attacker") {
                            textLabel = "[ATTACKER | "+val["ip"]+"] "+val["os"]+" "+val["arch"];
                        }

                        let normalAdding = true;
                        for (let i = 0; i < exportValue.length; i++) {
                            let exObj = exportValue[i];
                            if (exObj.id == val["ip"]) {
                                try {
                                    nodesArray.push({
                                        id: val["ip"],
                                        label: textLabel,
                                        image: "./assets/img/"+imgGenerator(val["os"], val["state"]), shape: 'image',
                                        x: exObj.x,
                                        y: exObj.y
                                    });
                                } catch (e) {
                                    nodesArray.update({
                                        id: val["ip"],
                                        label: textLabel,
                                        image: "./assets/img/"+imgGenerator(val["os"], val["state"]),
                                        shape: 'image',
                                        x: exObj.x,
                                        y: exObj.y});
                                }
                                normalAdding = false;
                                break;
                            }
                        }

                        if (normalAdding === true) {
                            try {
                                nodesArray.add({
                                    id: val["ip"],
                                    label: textLabel,
                                    image: "./assets/img/"+imgGenerator(val["os"], val["state"]), shape: 'image'
                                });
                            } catch (e) {
                                nodesArray.update({
                                    id: val["ip"],
                                    label: textLabel,
                                    image: "./assets/img/"+imgGenerator(val["os"], val["state"]),
                                    shape: 'image'});                            }
                        }
                        edgesArray.push({from: val["parent"], to: val["ip"], length: 40000000});
                    });
                    let container = document.getElementById('topology');
                    let data = {
                        nodes: nodesArray,
                        edges: edgesArray
                    };
                    let options = {
                        width: "1920px",
                        autoResize: false,
                        physics: {
                            enabled: false,
                            repulsion: {
                                nodeDistance: 40000000,
                                centralGravity: 0,
                                springLength: 40000000,
                                springConstant: 0.54,
                            },
                            stabilization: {
                                enabled: false
                            },
                            solver: 'forceAtlas2Based',
                            barnesHut: {
                                springLength:40000000,
                                springConstant: 100.54,
                                centralGravity: 0,
                                avoidOverlap: 1
                            }
                        },
                        edges: {
                            color: {
                                color: "#FFA500",
                                highlight: "#ffc901"
                            },
                            length: 40000000,
                            smooth: false,
                            width: 6
                        },
                        nodes: {
                            font: {
                                color: "#FFF",
                                size: 20,
                                face: "Kanit"
                            },
                            size: 32
                        }
                    };
                    network = new vis.Network(container, data, options, {manipulation:{enabled:true}});
                }
            }
        });

    };

    fetchDB();
    setInterval(fetchDB, 1600);
</script>
</body>
</html>