/**
 * BLOCK: really-simple-ssl-social
 *
 * Registering a basic block with Gutenberg.
 * Simple block, renders and saves the same content without any interactivity.
 */

//  Import CSS.
import './style.scss';
import './editor.scss';

import * as api from './utils/api';

const { __ } = wp.i18n; // Import __() from wp.i18n
const { registerBlockType } = wp.blocks; // Import registerBlockType() from wp.blocks
const { Component } = wp.element;
const el = wp.element.createElement;

class selectButtons extends Component {
	// Method for setting the initial state.
	static getInitialState() {
		return {
			html: '',
		};
	}

	// Constructing our component. With super() we are setting everything to 'this'.
	// Now we can access the attributes with this.props.attributes
	constructor() {
		super(...arguments);
		// Maybe we have a previously selected document. Try to load it.
		this.state = this.constructor.getInitialState(this.props.attributes.selectedType);

		// Bind so we can use 'this' inside the method.
		this.getDocuments = this.getDocuments.bind(this);
		this.getDocuments();
	}

	getDocuments( args = { } ) {

		return (api.getButtons(wp.data.select("core/editor").getCurrentPostId( ) ) ).then( ( response ) => {

			let html = response.data;
				//this.state.documents = documents;
				this.setState( { html } );

		});
	}

	render() {
		const { className, attributes: {} = {} } = this.props;

		let output = __('Loading...', 'really-simple-ssl-soc');
		let id = 'rsssl-social-share-buttons';

		// //load content
		if (this.state.html) {
			output = this.state.html;
		}
		return [
			<div key={id} className={className} dangerouslySetInnerHTML={ { __html: output } }></div>
		]
	}

}



/**
 * Register: a Gutenberg Block.
 *
 * Registers a new block provided a unique name and an object defining its
 * behavior. Once registered, the block is made editor as an option to any
 * editor interface where blocks are implemented.
 *
 * @link https://wordpress.org/gutenberg/handbook/block-api/
 * @param  {string}   name     Block name.
 * @param  {Object}   settings Block settings.
 * @return {?WPBlock}          The block, if it has been successfully
 *                             registered; otherwise `undefined`.
 */
registerBlockType( 'rsssl/block-rsssl-social', {
	// Block name. Block names must be string that contains a namespace prefix. Example: my-plugin/my-custom-block.
	title: __( 'Social share buttons' ), // Block title.
	icon: 'share', // Block icon from Dashicons → https://developer.wordpress.org/resource/dashicons/.
	category: 'common', // Block category — Group blocks together based on common traits E.g. common, formatting, layout widgets, embed.
	keywords: [
		__( 'shares' ),
		__( 'social' ),
		__( 'likes' ),
	],
	attributes: {
		html: {
			type: 'string',
			source: 'children',
			selector: 'p',
		},
	},

	/**
	 * The edit function describes the structure of your block in the context of the editor.
	 * This represents what the editor will render when the block is used.
	 *
	 * The "edit" property must be a valid function.
	 *
	 * @link https://wordpress.org/gutenberg/handbook/block-api/block-edit-save/
	 */
	edit:selectButtons,

	/**
	 * The save function defines the way in which the different attributes should be combined
	 * into the final markup, which is then serialized by Gutenberg into post_content.
	 *
	 * The "save" property must be specified and must be a valid function.
	 *
	 * @link https://wordpress.org/gutenberg/handbook/block-api/block-edit-save/
	 */
	save: function() {
		// Rendering in PHP
		return null;
	},
} );
