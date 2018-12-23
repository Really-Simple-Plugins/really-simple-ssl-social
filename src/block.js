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

class selectButtons extends Component {
	// Method for setting the initial state.
	static getInitialState(selectedType) {
		return {
			documents: [],
			selectedType: selectedType,
			document: {},
			hasDocuments: true,
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

		this.onChangeSelectDocument = this.onChangeSelectDocument.bind(this);
	}

	getDocuments(args = {}) {
		return (api.getDocuments()).then( ( response ) => {
			let documents = response.data;
			if( documents && 0 !== this.state.selectedType ) {
				// If we have a selected document, find that document and add it.
				const document = documents.find( ( item ) => { return item.id == this.state.selectedType } );

				// This is the same as { document: document, documents: documents }
				//this.state.documents = documents;
				this.setState( { document, documents } );
			} else {
				//this.state.documents = documents;
				this.setState({ documents });
			}
		});
	}

	onChangeSelectDocument(value) {
		const document = this.state.documents.find((item) => {
			return item.id === value
		});

		// Set the state
		this.setState({selectedDocument: value, document});

		// Set the attributes
		this.props.setAttributes({
			selectedDocument: value,
		});

	}

	render() {
		const { className, attributes: {} = {} } = this.props;

		let options = [{value: 0, label: __('Select a document', 'complianz')}];
		let output = __('Loading...', 'complianz');
		let id = 'document-title';

		if (!this.props.attributes.hasDocuments){
			output = __('No documents found. Please finish the Complianz Privacy Suite to generate documents', 'complianz');
			id = 'no-documents';
		}

		//build options
		if (this.state.documents.length > 0) {
			if (!this.props.isSelected){
				output = __('Click this block to show the options', 'complianz');
			} else {
				output = __('Select a document type from the dropdownlist', 'complianz');
			}
			this.state.documents.forEach((document) => {
				options.push({value: document.id, label: document.title});
			});
		}

		//load content
		if (this.props.attributes.selectedType!==0 && this.state.document && this.state.document.hasOwnProperty('title')) {
			output = this.state.document.content;
			id = this.props.attributes.selectedType;
		}

		return [
			!!this.props.isSelected && (
				<InspectorControls key='inspector'>
					<SelectControl onChange={this.onChangeSelectDocument} value={this.props.attributes.selectedType} label={__('Select a document', 'complianz')}
								   options={options}/>
				</InspectorControls>
			),
			<div key={id} className={className} dangerouslySetInnerHTML={ { __html: output } }></div>
		]
	}

}



/**
 * Register: aa Gutenberg Block.
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
	icon: 'shield', // Block icon from Dashicons → https://developer.wordpress.org/resource/dashicons/.
	category: 'common', // Block category — Group blocks together based on common traits E.g. common, formatting, layout widgets, embed.
	keywords: [
		__( 'shares' ),
		__( 'social' ),
		__( 'likes' ),
	],
	attributes: {
		content: {
			type: 'string',
			source: 'children',
			selector: 'p',
		},
		selectedType: {
			type: 'string',
			default: '',
		},
		documents: {
			type: 'array',
		},
		document: {
			type: 'array',
		}
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
