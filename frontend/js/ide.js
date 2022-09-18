const BOILER_PLATE_CODE = {
	c: `#include <stdio.h>
int main() {
	// your code goes here
	printf("Hello World");
	return 0;
}`,
	cpp: `#include <iostream>
using namespace std;
int main() {
	// your code goes here
	cout<<"Hello World!";
	return 0;
}`,
	python: `# your code goes here
print("Hello World")`,
	java: `import java.util.*;
public class Main {
	public static void main(String[] args){
		// your code goes here
		System.out.println("Hello World");
	}
}`,

	javascript: `// your code goes here
console.log("Hello World");`,
	php: `<?php
	// your code goes here
	echo "Hello World";
?>`,
};

const USER_CODE = {};

let editor;

window.onload = function () {
	ace.require("ace/ext/language_tools");
	editor = ace.edit("editor");
	editor.setTheme("ace/theme/twilight");
	editor.session.setMode("ace/mode/c_cpp");
	editor.setValue(BOILER_PLATE_CODE["c"]);
	editor.session.setUseWrapMode(true);
	editor.session.setTabSize(4);
	editor.setFontSize("20px");
	editor.setOption({
		enableBasicAutocompletion: true,
		enableLiveAutocompletion: true,
	});
	editor.getSession().on("change", function () {
		let language = $("#languages").val().toLowerCase();
		USER_CODE[language] = editor.getValue();
	});
};

function changeLanguage() {
	let language = $("#languages").val();

	if (language == "c" || language == "cpp")
		editor.session.setMode("ace/mode/c_cpp");
	else editor.session.setMode("ace/mode/" + language);

	language = language.toLowerCase();
	if (language in USER_CODE && USER_CODE[language] !== "") {
		editor.setValue(USER_CODE[language]);
	} else {
		editor.setValue(BOILER_PLATE_CODE[language]);
	}
}

function changeTheme() {
	let theme = $("#theme").val();
	editor.setTheme("ace/theme/" + theme);
}

async function executeCode() {
	$("#output").val("");
	$(".execution_time_div").css("display", "none");
	$("#execution_time").text("");
	$(".loader").css("display", "block");

	let code = editor.getValue();
	// Save code to a local text file, after that hit the request, and then delete the file
	const fmData = new FormData();
	fmData.append("code", code);
	fmData.append("language", $("#languages").val().toUpperCase());
	fmData.append("input_var", $("#input").val());
	let status = 1;

	await fetch("http://172.16.95.145/compiler/app/compiler/code", {
		method: "POST",
		body: fmData,
	})
		.then((response) => response.json())
		.then((data) => {
			if (data.status === 0) {
				$("#output").val(data.message);
				$("#output").css("color", "red");
			} else {
				$("#output").css("color", "black");
				$("#output").val(data.output);
				$(".execution_time_div").css("display", "block");
				$("#execution_time").text(
					parseFloat(data.execution_time).toFixed(3),
				);
			}
			$(".loader").css("display", "none");
			status = 0;
		});

	if (status === 1) {
		setTimeout(() => {
			$(".loader").css("display", "none");
		}, 5000);
	}
}
