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

registerBlockType( 'tss/season-match-list', {
	edit( { attributes, setAttributes } ) {
		const seasonOptions = usePostOptions( 'tss-seasons' );
		const matchTypeOptions = usePostOptions( 'tss-matchtypes' );

		return (
			<>
				<InspectorControls>
					<PanelBody title={ __( 'Season Match List Settings', 'tss' ) }>
						<SelectControl
							label={ __( 'Season', 'tss' ) }
							value={ attributes.seasonId || 0 }
							options={ seasonOptions }
							onChange={ ( value ) => setAttributes( { seasonId: Number( value ) } ) }
						/>
						<SelectControl
							label={ __( 'Match Type', 'tss' ) }
							value={ attributes.matchTypeId || 0 }
							options={ matchTypeOptions }
							onChange={ ( value ) => setAttributes( { matchTypeId: Number( value ) } ) }
						/>
					</PanelBody>
				</InspectorControls>
				<ServerSideRender block="tss/season-match-list" attributes={ attributes } />
			</>
		);
	},
	save() {
		return null;
	},
} );
