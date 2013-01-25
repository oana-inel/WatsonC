<?php 

$content_type = "application/json";
$api_key = "c6b735ba497e64428c6c61b488759583298c2cf3";
$url = "http://api.crowdflower.com/v1/jobs.json?key=".$api_key;
$uploadDirectory = "Files/";
$file = $_FILES['uploadedfile']['name'];

/* useful functions for printing the results from the web server */
function objectToArray($obj) {
	if (is_object($obj)) {
		$obj = get_object_vars($obj);
	}
	if (is_array($obj)) {
		return array_map(__FUNCTION__, $obj);
	}
	else {
		return $obj;
	}
}

function arrayToObject($d) {
	if (is_array($d)) {
		return (object) array_map(__FUNCTION__, $d);
	}
	else {
		return $d;
	}
}

/* wrap the attributes with the prefix "job" */
function prefixDataKeys($data, $prefix) {
      $newdata = array();

      foreach ($data as $key => $value) {
          $newkey = "$prefix" . '[' . $key . ']';
          $newdata[$newkey] = $value;
      }

      return $newdata;
}

/* create the settings' array */
$data = array();
$data["title"] = "Choose the valid RELATION(s) between the TERMS in the SENTENCE?";
$data["judgments_per_unit"] = $_POST["judgments_per_unit"];
$data["max_judgments_per_worker"] = $_POST["max_judgments_per_worker"];
$data["units_per_assignment"] = $_POST["units_per_assignment"];
$data["max_judgments_per_ip"] = $_POST["max_judgments_per_ip"];
$data["instructions"] = "STEP 1: Carefully read the SENTENCE below and select all the RELATION TYPE(s) that you think are expressed between the TWO HIGHLIGHTED WORDS in the text.  Note that if one of the WORDS appears multiple time you will have to consider only the highlighted one.\n\nSTEP 2a: Select the words from the text that support or indicate that the selected RELATION TYPE holds. \n\n         Example 1:  \n         for the relation 'PREVENTS' between 'INFLUENZA' and 'VITAMIN C' \n         in the sentence \".... the risk of influenza is reduced by vitamin C...\"\n         paste here the words: \"reduced by\"\n\n         Example 2: \n         for the relation 'DIAGNOSE' between 'RINNE TEST' and 'HEARING LOSS' \n         in the sentence \" ... RINNE test is used for determining hearing loss ...\"\n         paste here the words: \"used for determining\"\n\nSTEP 2b: If you select 'NONE' in STEP 1, then explain why do you think there is no relationship between the two words in the sentence.\n\nNOTE: You are not expected to have a domain knowledge in the topic of the text. It doesn't matter if you don't know what the highlighted words mean. It is important to understand what the different relation types mean (in STEP 1).";
$data["cml"] = "<p>In the sentence:&#160;<strong><em>\"</em></strong>\n{{sentence}}<strong><em>\"</em></strong></p>\n<p>Is<strong>&#160;</strong>\n{{term1}}<strong>&#160;</strong>&#160;<em><strong>----</strong>related-to<strong>----&#160;</strong></em>&#160;\n{{term2}}?</p>\n<p><strong></strong></p>\n<cml:checkboxes label=\"STEP 1: Select the valid RELATION(s)\" class=\"\" instructions=\"It is important that you understand what the different relation types mean. Definitions and examples are given in each choice\" validates=\"required\" aggregation=\"avg\"><cml:checkbox label=\"[TREATS:] therapeutic use of an ingredient or a drug, e.g. penicillin cures infection, etc.\"/><cml:checkbox label=\"[PREVENTS:] preventative use of an ingredient or a drug, e.g. vitamin C reduces the risk of influenza, etc.\"/><cml:checkbox label=\"[DIAGNOSED_BY_TEST_OR_DRUG:] diagnostic use of an ingredient, test or a drug, e.g.  RINNE test is used for determining hearing loss, etc.\" id=\"\"/><cml:checkbox label=\"[CAUSES:] the underlying reason for a symptom or a disease, e.g. fever induces dizziness etc.\" id=\"\"/><cml:checkbox label=\"[LOCATION:] body part or anatomical structure in which disease or disorder is observed, e.g. leukimia is found in the circulatory system, etc.\" id=\"\"/><cml:checkbox label=\"[SYMPTOM:] deviation from normal function indicating the presence of disease or abnormality, e.g. pain is a symptom of a broken arm, etc.\" id=\"\"/><cml:checkbox label=\"[MANIFESTATION:] links disorders to the observations (manifestations) that are closely associated with them, e.g. abdominal distension is a manifestation of liver failure\" id=\"\"/><cml:checkbox label=\"[CONTRAINDICATES:] a condition that indicates that drug or treatment SHOULD NOT BE USED, e.g. patients with obesity should avoid using danazol\" id=\"\"/><cml:checkbox label=\"[ASSOCIATED_WITH:] signs, symptoms or findings that often appear together, e.g. patients who smoke often have yellow teeth.\" id=\"\"/><cml:checkbox label=\"[SIDE_EFFECT:] a secondary condition or symptom that results from a drug or treatment, e.g. use of antidepressants causes dryness in the eyes.\" id=\"\"/><cml:checkbox label=\"[IS_A:] a relation that indicates that one of the terms is more specific variation of the other, e.g. migraine is a kind of headache. \" id=\"\"/><cml:checkbox label=\"[PART_OF]: an anatomical or structural sub-component, e.g. the left ventrical is part of the heart\" id=\"\"/><cml:checkbox label=\"[NONE]: the words are related, but not by any of the above relations\" id=\"\"/><cml:checkbox label=\"[NONE]: there is no relation between those words in this sentence\" id=\"\"/></cml:checkboxes>\n\n<cml:textarea label=\"STEP 2a: Copy &amp; Paste ONLY the words from the SENTENCE that express the RELATION you selected in STEP1\" class=\"\" instructions=\"Copy &amp; Paste from the sentence ONLY the words that express the RELATION you have selected in STEP1. DO NOT copy the whole sentence.\" validates=\"required\" default=\"Answer N/A if you selected [NONE] in STEP 1. DO NOT copy the whole sentence.\"/>\n        <cml:textarea label=\"STEP 2b: If you selected [NONE] in STEP 1, explain why\" class=\"\" instructions=\"If you think there is a relation between those two words, but it is different than any of the relations in STEP 1, then type the relation here.   If you think there is no relation between those terms, explain why do you think it is.\" default=\"Answer N/A if you have selected a relation in STEP 1 other than [NONE].\" validates=\"required\"/>";

/* create the job with the specified settings */
$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(prefixDataKeys($data, "job")));
curl_setopt($ch, CURLOPT_URL, $url);

$response = json_decode(curl_exec($ch));
$info = curl_getinfo($ch);
$array = objectToArray($response);

/* job id */
$job_id = $array["id"];

/* print the responses from the CrowdFlower server */
//print_r(objectToArray($response));
//print_r(objectToArray($info));

/* create cURL query for including countries */
$included_countries_query = "curl -X PUT -d \"job[included_countries][]=AU&job[included_countries][]=CA&job[included_countries][]=GB&job[included_countries][]=US\" \"https://api.crowdflower.com/v1/jobs/".$job_id.".json?key=".$api_key."\"";
exec($included_countries_query);

/* create cURL query for excluding countries */
$excluded_countries_query = "curl -X PUT -d \"job[excluded_countries][]=IN&job[excluded_countries][]=CN&job[excluded_countries][]=ID\" \"https://api.crowdflower.com/v1/jobs/".$job_id.".json?key=".$api_key."\"";
exec($excluded_countries_query);

/* create cURL query for adding options */
$options_query = "curl -X PUT -d \"job[options][mail_to]=oana.inel@gmail.com&job[options][keywords]=relations-annotation natural-language-processing text-annotation medical-relations&job[options][include_unfinished]=true&job[options][tags]=natural-language-processing\" \"https://api.crowdflower.com/v1/jobs/".$job_id.".json?key=".$api_key."\"";
exec($options_query);

/* add channels to the job */
$set_channels_query = "curl -d \"channels[]=amt&channels[]=mob\" \"https://api.crowdflower.com/v1/jobs/$job_id/channels?key=$api_key\"";
exec($set_channels_query);

/* upload the data to be annotated into the new job */ 
$upload_query = "curl -T \"$file\" -H \"Content-Type: text/csv\" \"https://api.crowdflower.com/v1/jobs/$job_id/upload.json?key=$api_key\"";
$response = exec($upload_query);

/* print the responses from the CrowdFlower server */
print_r(objectToArray($response));
//print_r(objectToArray($info));

?>

