var menuStyle = {
    menu: 'context_menu',
    menuSeparator: 'context_menu_separator'
};

function createMapContextMenu(map) {
    return createContextMenu(map, map,
        [{ label:'Add Marker', id:'menu_add_marker', className: 'context_menu_item', eventName:'menu_add_marker_clicked' }]
    )
}

function createMarkerContextMenu(map,  marker) {
    return createContextMenu(map, marker,
        [
            { label:'Add Marker After', id:'menu_add_marker_after',
                className: 'context_menu_item', eventName:'menu_add_marker_after_clicked' },
            { label:'Delete Marker', id:'menu_delete_marker',
                className: 'context_menu_item', eventName:'menu_delete_marker_clicked' }
        ]
    )
}

function createContextMenu(map, target, menuItems) {
    var contextMenuOptions  = {
        classNames: menuStyle,
        menuItems: menuItems,
        pixelOffset: new google.maps.Point(10, -5),
        zIndex: 5,
        classNames: {menu:'context_menu', menuSeparator:'context_menu_separator'}
    };

    var contextMenu = new ContextMenu(map, contextMenuOptions);

    // Add listener to right click
    google.maps.event.addListener(target, 'rightclick', function(mouseEvent) {
        contextMenu.show(mouseEvent.latLng, target);
    });

    return contextMenu;
}
