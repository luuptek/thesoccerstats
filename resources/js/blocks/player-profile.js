import { registerBlockType } from '@wordpress/blocks';
import { InspectorControls } from '@wordpress/block-editor';
import { PanelBody, SelectControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import ServerSideRender from '@wordpress/server-side-render';
import { useSelect } from '@wordpress/data';

const usePostOptions = ( postType ) => {
	const posts = useSelect(
		( select ) => select( 'core' ).getEntityRecords( 'postType', postType, { per_page: -1, orderby: 'title', order: 'asc' } ),
		[ postType ]
	);

	return [
		{ label: __( 'Select', 'tss' ), value: 0 },
		...( posts || [] ).map( ( post ) => ( {
			label: post.title.rendered || __( '(no title)', 'tss' ),
			value: post.id,
		} ) ),
	];
};

registerBlockType( 'tss/player-profile', {
	edit( { attributes, setAttributes } ) {
		const options = usePostOptions( 'tss-players' );

		return (
			<>
				<InspectorControls>
					<PanelBody title={ __( 'Player Profile Settings', 'tss' ) }>
						<SelectControl
							label={ __( 'Player', 'tss' ) }
							value={ attributes.playerId || 0 }
							options={ options }
							onChange={ ( value ) => setAttributes( { playerId: Number( value ) } ) }
						/>
					</PanelBody>
				</InspectorControls>
				<ServerSideRender block="tss/player-profile" attributes={ attributes } />
			</>
		);
	},
	save() {
		return null;
	},
} );
