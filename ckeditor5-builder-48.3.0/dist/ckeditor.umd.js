(function(e,t){typeof exports==`object`&&typeof module<`u`?t(require("ckeditor5")):typeof define==`function`&&define.amd?define([`ckeditor5`],t):(e=typeof globalThis<`u`?globalThis:e||self,t(e.CKEditor5))})(this,function(e){var t={attachTo:document.querySelector(`#editor`),root:{placeholder:`Type or paste your content here!`,initialData:`<h2>Congratulations on setting up CKEditor 5! 🎉</h2>
<p>
	You've successfully created a CKEditor 5 project. This powerful text editor
	will enhance your application, enabling rich text editing capabilities that
	are customizable and easy to use.
</p>
<h3>What's next?</h3>
<ol>
	<li>
		<strong>Integrate into your app</strong>: time to bring the editing into
		your application. Take the code you created and add to your application.
	</li>
	<li>
		<strong>Explore features:</strong> Experiment with different plugins and
		toolbar options to discover what works best for your needs.
	</li>
	<li>
		<strong>Customize your editor:</strong> Tailor the editor's
		configuration to match your application's style and requirements. Or
		even write your plugin!
	</li>
</ol>
<p>
	Keep experimenting, and don't hesitate to push the boundaries of what you
	can achieve with CKEditor 5. Your feedback is invaluable to us as we strive
	to improve and evolve. Happy editing!
</p>
<h3>Helpful resources</h3>
<p>
	<i>An editor without the </i><code>Link</code>
	<i>plugin? That's brave! We hope the links below will be useful anyway </i>😉
</p>
<ul>
	<li>📝 Trial sign up: https://portal.ckeditor.com/checkout?plan=free,</li>
	<li>📕 Documentation: https://ckeditor.com/docs/ckeditor5/latest/installation/index.html,</li>
	<li>⭐️ GitHub (star us if you can!): https://github.com/ckeditor/ckeditor5,</li>
	<li>🏠 CKEditor Homepage: https://ckeditor.com,</li>
	<li>🧑‍💻 CKEditor 5 Demos: https://ckeditor.com/ckeditor-5/demo/</li>
</ul>
<h3>Need help?</h3>
<p>
	See this text, but the editor is not starting up? Check the browser's
	console for clues and guidance. It may be related to an incorrect license
	key if you use premium features or another feature-related requirement. If
	you cannot make it work, file a GitHub issue, and we will help as soon as
	possible!
</p>
`},toolbar:{items:[`undo`,`redo`,`|`,`heading`,`|`,`bold`,`italic`,`underline`,`strikethrough`,`subscript`,`superscript`,`|`,`horizontalLine`,`highlight`,`blockQuote`,`|`,`alignment`,`|`,`bulletedList`,`numberedList`,`todoList`,`outdent`,`indent`],shouldNotGroupWhenFull:!1},plugins:[e.Alignment,e.Autosave,e.BlockQuote,e.Bold,e.Essentials,e.GeneralHtmlSupport,e.Heading,e.Highlight,e.HorizontalLine,e.Indent,e.IndentBlock,e.Italic,e.List,e.Paragraph,e.Strikethrough,e.Subscript,e.Superscript,e.TodoList,e.Underline],licenseKey:`GPL`,autosave:{},heading:{options:[{model:`paragraph`,title:`Paragraph`,class:`ck-heading_paragraph`},{model:`heading1`,view:`h1`,title:`Heading 1`,class:`ck-heading_heading1`},{model:`heading2`,view:`h2`,title:`Heading 2`,class:`ck-heading_heading2`},{model:`heading3`,view:`h3`,title:`Heading 3`,class:`ck-heading_heading3`},{model:`heading4`,view:`h4`,title:`Heading 4`,class:`ck-heading_heading4`},{model:`heading5`,view:`h5`,title:`Heading 5`,class:`ck-heading_heading5`},{model:`heading6`,view:`h6`,title:`Heading 6`,class:`ck-heading_heading6`}]},htmlSupport:{allow:[{name:/^.*$/,styles:!0,attributes:!0,classes:!0}]}};e.ClassicEditor.create(t)});