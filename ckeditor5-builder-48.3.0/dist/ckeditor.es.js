import { Alignment as e, Autosave as t, BlockQuote as n, Bold as r, ClassicEditor as i, Essentials as a, GeneralHtmlSupport as o, Heading as s, Highlight as c, HorizontalLine as l, Indent as u, IndentBlock as d, Italic as f, List as p, Paragraph as m, Strikethrough as h, Subscript as g, Superscript as _, TodoList as v, Underline as y } from "ckeditor5";
//#region main.js
var b = {
	attachTo: document.querySelector("#editor"),
	root: {
		placeholder: "Type or paste your content here!",
		initialData: "<h2>Congratulations on setting up CKEditor 5! 🎉</h2>\n<p>\n	You've successfully created a CKEditor 5 project. This powerful text editor\n	will enhance your application, enabling rich text editing capabilities that\n	are customizable and easy to use.\n</p>\n<h3>What's next?</h3>\n<ol>\n	<li>\n		<strong>Integrate into your app</strong>: time to bring the editing into\n		your application. Take the code you created and add to your application.\n	</li>\n	<li>\n		<strong>Explore features:</strong> Experiment with different plugins and\n		toolbar options to discover what works best for your needs.\n	</li>\n	<li>\n		<strong>Customize your editor:</strong> Tailor the editor's\n		configuration to match your application's style and requirements. Or\n		even write your plugin!\n	</li>\n</ol>\n<p>\n	Keep experimenting, and don't hesitate to push the boundaries of what you\n	can achieve with CKEditor 5. Your feedback is invaluable to us as we strive\n	to improve and evolve. Happy editing!\n</p>\n<h3>Helpful resources</h3>\n<p>\n	<i>An editor without the </i><code>Link</code>\n	<i>plugin? That's brave! We hope the links below will be useful anyway </i>😉\n</p>\n<ul>\n	<li>📝 Trial sign up: https://portal.ckeditor.com/checkout?plan=free,</li>\n	<li>📕 Documentation: https://ckeditor.com/docs/ckeditor5/latest/installation/index.html,</li>\n	<li>⭐️ GitHub (star us if you can!): https://github.com/ckeditor/ckeditor5,</li>\n	<li>🏠 CKEditor Homepage: https://ckeditor.com,</li>\n	<li>🧑‍💻 CKEditor 5 Demos: https://ckeditor.com/ckeditor-5/demo/</li>\n</ul>\n<h3>Need help?</h3>\n<p>\n	See this text, but the editor is not starting up? Check the browser's\n	console for clues and guidance. It may be related to an incorrect license\n	key if you use premium features or another feature-related requirement. If\n	you cannot make it work, file a GitHub issue, and we will help as soon as\n	possible!\n</p>\n"
	},
	toolbar: {
		items: [
			"undo",
			"redo",
			"|",
			"heading",
			"|",
			"bold",
			"italic",
			"underline",
			"strikethrough",
			"subscript",
			"superscript",
			"|",
			"horizontalLine",
			"highlight",
			"blockQuote",
			"|",
			"alignment",
			"|",
			"bulletedList",
			"numberedList",
			"todoList",
			"outdent",
			"indent"
		],
		shouldNotGroupWhenFull: !1
	},
	plugins: [
		e,
		t,
		n,
		r,
		a,
		o,
		s,
		c,
		l,
		u,
		d,
		f,
		p,
		m,
		h,
		g,
		_,
		v,
		y
	],
	licenseKey: "GPL",
	autosave: {},
	heading: { options: [
		{
			model: "paragraph",
			title: "Paragraph",
			class: "ck-heading_paragraph"
		},
		{
			model: "heading1",
			view: "h1",
			title: "Heading 1",
			class: "ck-heading_heading1"
		},
		{
			model: "heading2",
			view: "h2",
			title: "Heading 2",
			class: "ck-heading_heading2"
		},
		{
			model: "heading3",
			view: "h3",
			title: "Heading 3",
			class: "ck-heading_heading3"
		},
		{
			model: "heading4",
			view: "h4",
			title: "Heading 4",
			class: "ck-heading_heading4"
		},
		{
			model: "heading5",
			view: "h5",
			title: "Heading 5",
			class: "ck-heading_heading5"
		},
		{
			model: "heading6",
			view: "h6",
			title: "Heading 6",
			class: "ck-heading_heading6"
		}
	] },
	htmlSupport: { allow: [{
		name: /^.*$/,
		styles: !0,
		attributes: !0,
		classes: !0
	}] }
};
i.create(b);
//#endregion
