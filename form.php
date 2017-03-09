<!DOCTYPE HTML>
<!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
<link rel="stylesheet" href="css/hs.css">
<html>
<head>
<style>
.error {color: #FF0000;}
</style>
</head>
<body>

<?php
// define variables and set to empty values
$nameErr = $liveVolErr = $DRVolErr = $fileSysErr = $replicatedErr = $incGBErr = $v7000GBErr = $GMCVErr = "";
$name = $liveVol = $DRVol = $fileSys = $replicated = $incGB = $v7000 = $GMCV = "";
$v7000Host_A = array("gen1"=>"HRS-A-V7000-01", "gen2"=>"HRS-A-V7000-02");
$v7000Host_B = array("gen1"=>"HRS-B-V7000-01", "gen2"=>"HRS-B-V7000-02");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  if (empty($_POST["v7000"])) {
    $v7000Err = "Required";
  } else {
    $v7000 = test_input($_POST["v7000"]);
  }

  if (empty($_POST["name"])) {
    $nameErr = "Host is required";
  } else {
    $name = test_input($_POST["name"]);
    // check if name only contains letters and whitespace
    if (!preg_match("/^[a-zA-Z0-9-]*$/",$name)) {
      $nameErr = "Only letters and digits and hypen allowed";
    }
  }

  if (empty($_POST["liveVol"])) {
    $liveVolErr = "Live volume name is required";
  } else {
    $liveVol = test_input($_POST["liveVol"]);
    // check if volume name is valid
    if (!preg_match("/^[a-zA-Z0-9_]*$/",$liveVol)) {
      $liveVolErr = "Only letters and digits and underscore allowed";
    }
  }

  if (empty($_POST["DRVol"])) {
    $DRVolErr = "DR volume name is required";
  } else {
    $DRVol = test_input($_POST["DRVol"]);
    // check if volume name is valid
    if (!preg_match("/^[a-zA-Z0-9_]*$/",$DRVol)) {
      $DRVolErr = "Only letters and digits and underscore allowed";
    }
  }

  if (empty($_POST["replicated"])) {
    $replicatedErr = "Required";
  } else {
    $replicated = test_input($_POST["replicated"]);
  }

  if (empty($_POST["GMCV"])) {
    $GMCVErr = "Required";
  } else {
    $GMCV = $_POST["GMCV"];
  }

  if (empty($_POST["fileSys"])) {
    $fileSysErr = "File system name is required";
  } else {
    $fileSys = test_input($_POST["fileSys"]);
    // check if file system name is valid
    if (!preg_match("/^\/[a-zA-Z0-9_]*$/",$fileSys)) {
      $fileSysErr = "Only letters and digits and underscore allowed";
    }
  }

  if (empty($_POST["incGB"])) {
    $incGBErr = "Amount to increase by is required";
  } else {
    $incGB = test_input($_POST["incGB"]);
    // check if URL address syntax is valid
    if (!preg_match("/^[0-9]*$/",$incGB)) {
      $incGBErr = "Digits only";
    }
  }

  //$file = fopen("/tmp/test.txt","w") or die("Unable to open file!");
  $ctask_01_desc = ctask_break_rep($liveVol, $DRVol, $v7000Host_B[$v7000]); // Break replication
  $ctask_02_desc = ctask_extend_live($DRVol, $v7000Host_B[$v7000], $incGB); // Expand DR volume
  $ctask_03_desc = ctask_extend_DR($liveVol, $v7000Host_A[$v7000], $incGB); // Expand live volume
  $ctask_04_desc = ctask_create_mirror($liveVol, $DRVol, $v7000Host_A[$v7000], $v7000Host_B[$v7000], $GMCV); // Recreate mirror

  //fclose($file);

}

//echo("!".$v7000Host[$v7000]);
function ctask_break_rep($A, $B, $V) {
  //echo fwrite($file,"Hello World. Testing2!");
  //fprintf($file,"Break replication between %s and %s", $A, $B);
  //fprintf($file,"Log on to %s", $V); // Needs to be B host
  $_ = sprintf("<b>Break replication between %s and %s</b><br>", strtoupper($A), strtoupper($B));
  $_ = $_ . sprintf("Log on to %s<br>", strtoupper($V)); // Needs to be B host
  $_ = $_ . sprintf("Select Copy Services > Remote Copy<br>");
  $_ = $_ . sprintf("Filter for volume %s<br>", strtoupper($B));
  $_ = $_ . sprintf("Right click the relationship and select Delete Relationship<br><br>");
  return $_;
}

function ctask_extend_live($B, $V, $GB) {
  $_ = sprintf("<b>Extend V7000 volume %s</b><br>", strtoupper($B));
  $_ = $_ . sprintf("Log on to %s<br>", strtoupper($V)); // Needs to be B host
  $_ = $_ . sprintf("Select Volumes > Create Volumes<br>");
  $_ = $_ . sprintf("Filter for volume %s<br>", strtoupper($B));
  $_ = $_ . sprintf("Right click the volume and select Expand<br>");
  $_ = $_ . sprintf("Enter %s for the 'Expand by:' value<br>", $GB);
  $_ = $_ . sprintf("Confirm the 'Final size:' value is correct<br>");
  $_ = $_ . sPrintf("Click Expand<br><br>");
  return $_;
}

function ctask_extend_DR($A, $V, $GB) {
  $_ = sprintf("<b>Extend V7000 volume %s</b><br>", strtoupper($A));
  $_ = $_ . sprintf("Log on to %s<br>", strtoupper($V));
  $_ = $_ . sprintf("Select Volumes > Create Volumes<br>");
  $_ = $_ . sprintf("Filter for volume %s<br>", strtoupper($A));
  $_ = $_ . sprintf("Right click the volume and select Expand<br>");
  $_ = $_ . sprintf("Enter %s for the 'Expand by:' value<br>", $GB);
  $_ = $_ . sprintf("Confirm the 'Final size:' value is correct<br>");
  $_ = $_ . sPrintf("Click Expand<br><br>");
  return $_;
}

function ctask_create_mirror($A, $B, $VA, $VB, $CV) {
  $_ = sprintf("<b>Create mirror relationship between %s and %s</b><br>", strtoupper($A), strtoupper($B));
  $_ = $_ . sprintf("Log on to %s<br>", strtoupper($VA));
  $_ = $_ . sprintf("Select Copy Services > Remote Copy<br>");
  $_ = $_ . sprintf("Select Actions > Create Relationship<br>");
  if ($CV == "GMCVYes") {
    $_ = $_ . global_mirror_change_vol($A, $B, $VB);
  } else {
    $_ = $_ . global_mirror($A, $B, $VB);
  }
  return $_;
}

function global_mirror_change_vol($A, $B, $VB) {
  $_ = sprintf("Select Global Mirror with Change Volumes and click Next<br>");
  $_ = $_ . sprintf("Select On another system and select %s<br>", strtoupper($VB));
  $_ = $_ . sprintf("Select Master volume %s<br>", strtoupper($A));
  $_ = $_ . sprintf("Select Auxiliary volume %s<br>", strtoupper($B));
  $_ = $_ . sprintf("Click Add then click Next<br>");

  return $_;
}

function global_mirror($A, $B) {
  $_ = "This will be GM without change vol instruction";
  return $_;
}

function test_input($data) {
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}
?>

<div class="container">
   <div class="row">
<div class="col-sm-6">
  <h2>Expand a V7000 volume</h2>
  <p><span class="error">* required field.</span></p>

<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
  V7000:
  <span class="error">* <?php echo $v7000Err;?></span>
  <div class="radio-inline">
  <label><input type="radio" name="v7000" value="gen1">Gen 1</label>
  </div>
  <div class="radio-inline">
  <label><input type="radio" name="v7000" value="gen2">Gen 2</label>
  </div>
  <br><br>
  Host: <input type="text" name="name">
  <span class="error">* <?php echo $nameErr;?></span>
  <br><br>
  Live Volume: <input type="text" name="liveVol">
  <span class="error">* <?php echo $liveVolErr;?></span>
  <br><br>
  DR Volume: <input type="text" name="DRVol">
  <span class="error">* <?php echo $DRVolErr;?></span>
  <br><br>
  Replicated?:
  <span class="error">* <?php echo $replicatedErr;?></span>
  <div class="radio-inline">
  <label><input type="radio" name="replicated" value="repYes">Yes</label>
  </div>
  <div class="radio-inline">
  <label><input type="radio" name="replicated" value="repNo">No</label>
  </div>
  <br><br>
  GM with CV?:
  <span class="error">* <?php echo $GMCVErr;?></span>
  <div class="radio-inline">
  <label><input type="radio" name="GMCV" value="GMCVYes">Yes</label>
  </div>
  <div class="radio-inline">
  <label><input type="radio" name="GMCV" value="GMCVNo">No</label>
  </div>
  <br><br>
  Increase by: <input type="text" name="incGB">
  <span class="error">* <?php echo $incGBErr;?></span>
  <br><br>
  File system: <input type="text" name="fileSys">
  <span class="error">* <?php echo $fileSysErr;?></span>
  <br><br>
  <input type="submit" class="btn btn-info" value="Submit Button">
</form>
<div>
</div>
</div>


<div class="col-sm-6">
      <h2>CTASK Breakdown</h2>

<?php
//echo $v7000;
echo $ctask_01_desc;
echo $ctask_02_desc;
echo $ctask_03_desc;
echo $ctask_04_desc;
?>
</div>

</body>
</html>
