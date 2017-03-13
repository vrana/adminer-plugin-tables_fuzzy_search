<?php

/** Add fuzzy search in tables for Adminer
* @link https://github.com/brunetton/adminer-tables_fuzzy_search
* @author Bruno Duyé, https://github.com/brunetton
* @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2 (one or other)
*/

class AdminerTablesFuzzySearch {
	function tablesPrint($tables) {
		?>

<style media="screen" type="text/css">
#fuzzy_tables_search_result .selected a {
	color: red;
}
#fuzzy_tables_search_result a em {
	font-weight: bold;
	font-style: italic;
}
</style>

<script type="text/javascript">

	// Fuzzy - minified - https://github.com/bripkens/fuzzy.js
	(function(context){"use strict";var fuzzy=function fuzzy(term,query){var max=calcFuzzyScore(term,query);var termLength=term.length;if(fuzzy.analyzeSubTerms){for(var i=1;i<termLength&&i<fuzzy.analyzeSubTermDepth;i++){var subTerm=term.substring(i);var score=calcFuzzyScore(subTerm,query);if(score.score>max.score){score.term=term;score.highlightedTerm=term.substring(0,i)+score.highlightedTerm;max=score}}}return max};var calcFuzzyScore=function calcFuzzyScore(term,query){var score=0;var termLength=term.length;var queryLength=query.length;var highlighting="";var ti=0;var previousMatchingCharacter=-2;for(var qi=0;qi<queryLength&&ti<termLength;qi++){var qc=query.charAt(qi);var lowerQc=qc.toLowerCase();for(;ti<termLength;ti++){var tc=term.charAt(ti);if(lowerQc===tc.toLowerCase()){score++;if(previousMatchingCharacter+1===ti){score+=2}highlighting+=fuzzy.highlighting.before+tc+fuzzy.highlighting.after;previousMatchingCharacter=ti;ti++;break}else{highlighting+=tc}}}highlighting+=term.substring(ti,term.length);return{score:score,term:term,query:query,highlightedTerm:highlighting}};fuzzy.matchComparator=function matchComparator(m1,m2){return m2.score-m1.score};fuzzy.analyzeSubTerms=false;fuzzy.analyzeSubTermDepth=10;fuzzy.highlighting={before:"<em>",after:"</em>"};if(typeof module!=="undefined"&&module.exports){module.exports=fuzzy}else if(typeof define==="function"){define(function(){return fuzzy})}else{var previousFuzzy=context.fuzzy;fuzzy.noConflict=function(){context.fuzzy=previousFuzzy;return fuzzy};context.fuzzy=fuzzy}})(this);

	function moveSelection(down) {
		var selectedNode = document.querySelector('#fuzzy_tables_search_result span.selected');
		var newSelectedNode = null;
		if (down) {
			newSelectedNode = selectedNode.nextElementSibling;
			if (newSelectedNode === null) {
				// last one => first one
				newSelectedNode = document.querySelectorAll('#fuzzy_tables_search_result span')[0];
			};
		} else {
			// up
			newSelectedNode = selectedNode.previousElementSibling;
			if (newSelectedNode === null) {
				// first one => last one
				nodes = document.querySelectorAll('#fuzzy_tables_search_result span');
				newSelectedNode = nodes[nodes.length - 1];
			};
		};
		if (newSelectedNode != null) {
			selectedNode.classList.remove('selected');
			newSelectedNode.classList.add('selected');
		}
	}

	function closeResults() {
		document.querySelector('#menu #fuzzy_tables_search_result').style.display = 'none';
	}

	function tablesFilter(query) {
		if (query === '') {
			closeResults();
			return;
		}
		// Get spans containing tables names and links
		var tables = document.querySelectorAll('#tables > a.select');
		var tablesData = new Array(tables.length);
		// tablesData is an array of objects with properties:
		//   - name: name of the table
		//   - score: fuzzysearch score
		//   - nodes: array containing the two links for opening table
		for (var i = 0; i < tables.length; i++) {
			var tableName = tables[i].nextSibling.nextSibling.text;
			var nodes = [tables[i], tables[i].nextSibling.nextSibling];
			var fuzzyData = fuzzy(tableName, query);
			tablesData[i] = {
				'name': tableName,
				'nodes': nodes,
				'fuzzyData': fuzzyData  // Object containing 'score', 'highlightedTerm', 'query' and 'term'
			};
		}
		// Sort by score and length
		tablesData.sort(function(m1, m2) {
			return (m2.fuzzyData.score - m1.fuzzyData.score != 0) ? m2.fuzzyData.score - m1.fuzzyData.score : m1.name.length - m2.name.length;
		});
		// console.log(tablesData.map(function(elem) {return String(elem.fuzzyData.score) + ' ' + elem.name}));
		// Add matches to results div
		maxResults = 15;
		resultsDiv = document.querySelector('#menu #fuzzy_tables_search_result');
		resultsDiv.style.display = 'inline';
		resultsDiv.innerHTML = '';
		for (var i = 0; i < maxResults; i++) {
			// debugger;
			spanNode = document.createElement('span');
			// link 1
			spanNode.appendChild(tablesData[i].nodes[0].cloneNode(true));
			spanNode.appendChild(document.createTextNode(' '));
			// link 2
			link_node = tablesData[i].nodes[1].cloneNode(true);
			link_node.innerHTML = tablesData[i].fuzzyData.highlightedTerm;
			spanNode.appendChild(link_node);
			spanNode.appendChild(document.createElement("br"));
			if (i == 0) {
				spanNode.classList.add('selected');
			};
			resultsDiv.appendChild(spanNode);
		}
		// debugger;
	};

</script>


<p class="jsonly" style="padding-bottom: 0; border-bottom: none;">
	<input id='fuzzy_tables_search_input' accesskey="F" onblur="closeResults()"/>
</p>
<div id="fuzzy_tables_search_result" style="
	margin: 0 0 0 1em;
	border: 1px solid #969696;
	position: absolute;
	background-color: #FCFCFF;
	padding: 0.2em 0.2em 0.2em 0.4em;
	width: 25em;
	overflow: hidden;
	display: none;">
</div>

<?php
		Adminer::tablesPrint();
?>

<script type="text/javascript">

	var delay = (function(){
		var timer = 0;
		return function(callback, ms) {
			clearTimeout (timer);
			timer = setTimeout(callback, ms);
		};
	})();

	document.querySelector('#fuzzy_tables_search_input').addEventListener('keyup', function(e) {
		if (e.keyCode === 13) { // enter
			// Open selected link
			url = document.querySelector('#menu #fuzzy_tables_search_result span.selected > a');
			if (e.shiftKey) {
				window.open(url, '_blank').focus();
			} else {
				window.location = url;
			}
		} else if (e.keyCode === 40) { // down
			moveSelection(true);
		} else if (e.keyCode === 38) { // up
			moveSelection(false);
		} else if (e.keyCode === 27) { // ESC
			closeResults();
		} else {
			delay(function() {
				tablesFilter(document.querySelector('#fuzzy_tables_search_input').value.replace(/ /g,''));
			}, 200);
		}
	}, false);
</script>

<?php
	}
}
