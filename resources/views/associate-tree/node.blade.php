@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/d3@7/dist/d3.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/html-to-image@1.11.11/dist/html-to-image.min.js"></script>

    <script>
        window.associateTreeConfig = {
            treeData: @json($treeData),
            rootAssociateId: @json($rootAssociate->associate_id ?? 'root'),
            rootAssociateName: @json($rootAssociate->associate_name ?? 'Root'),
        };
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            'use strict';
            /*
            |--------------------------------------------------------------------------
            | Configuration
            |--------------------------------------------------------------------------
            */
            const config = window.associateTreeConfig || {};
            if (!config.treeData) {
                return;
            }
            /*
            |--------------------------------------------------------------------------
            | Required elements
            |--------------------------------------------------------------------------
            */
            const svgElement = document.getElementById('associateTreeSvg');
            const exportContainer = document.getElementById('treeExportContainer');
            const scrollArea = document.getElementById('treeScrollArea');
            const tooltip = document.getElementById('treeNodeTooltip');
            const searchInput = document.getElementById('associateTreeSearch');
            const directionSelect = document.getElementById('associateTreeDirection');
            const applyFilterButton = document.getElementById('applyTreeFilter');
            const resetFilterButton = document.getElementById('resetTreeFilter');
            const visibleTreeCount = document.getElementById('visibleTreeCount');
            const filterStatus = document.getElementById('treeFilterStatus');
            const treeChartTitle = document.getElementById('treeChartTitle');
            const filterLoader = document.getElementById('treeFilterLoader');
            const downloadFilterText = document.getElementById('treeDownloadFilterText');
            if (!svgElement || !exportContainer || !scrollArea || !tooltip) {
                return;
            }
            /*
            |--------------------------------------------------------------------------
            | Original tree state
            |--------------------------------------------------------------------------
            */
            const originalTreeData = cloneTreeData(config.treeData);
            let currentTreeData = cloneTreeData(originalTreeData);
            let currentSvgWidth = 0;
            let currentSvgHeight = 0;
            let tooltipNodes = null;
            /*
            |--------------------------------------------------------------------------
            | Tree dimensions
            |--------------------------------------------------------------------------
            */
            const nodeRadius = 58;
            const rootRadius = 65;
            const horizontalGap = 185;
            const verticalGap = 185;
            const singleChildOffset = 82;
            const outerPaddingX = 85;
            const outerPaddingTop = 75;
            const outerPaddingBottom = 85;
            /*
            |--------------------------------------------------------------------------
            | Clone tree safely
            |--------------------------------------------------------------------------
            */
            function cloneTreeData(data) {
                return JSON.parse(JSON.stringify(data));
            }
            /*
            |--------------------------------------------------------------------------
            | Normalize filter values
            |--------------------------------------------------------------------------
            */
            function normalizeValue(value) {
                return String(value ?? '').trim().toLowerCase();
            }

            function nodeMatchesSearch(node, searchValue) {
                if (!node) {
                    return false;
                }
                const associateId = normalizeValue(node.associate_id);
                const associateName = normalizeValue(node.name);
                return (searchValue === '' || associateId === searchValue || associateId.includes(searchValue) ||
                    associateName.includes(searchValue));
            }
            /*
            |--------------------------------------------------------------------------
            | Find searched associate
            |--------------------------------------------------------------------------
            */
            function findAssociateNode(node, searchValue) {
                if (!node) {
                    return null;
                }
                if (nodeMatchesSearch(node, searchValue)) {
                    return node;
                }
                for (const child of (node.children || [])) {
                    const matchedNode = findAssociateNode(child, searchValue);
                    if (matchedNode) {
                        return matchedNode;
                    }
                }
                return null;
            }
            /*
            |--------------------------------------------------------------------------
            | Build selected associate team
            |--------------------------------------------------------------------------
            |
            | Search ID lagne par wahi associate root banega.
            | Direction left/right hone par us associate ki poori selected team aayegi.
            |
            */
            function buildTeamTree(node, directionValue) {
                if (!node) {
                    return null;
                }
                const clonedNode = cloneTreeData(node);
                if (!directionValue) {
                    return {
                        ...clonedNode,
                        side: 'root',
                    };
                }
                return {
                    ...clonedNode,
                    side: 'root',
                    children: (clonedNode.children || []).filter(function(child) {
                        return normalizeValue(child.side) === directionValue;
                    }),
                };
            }
            /*
            |--------------------------------------------------------------------------
            | Get filtered data
            |--------------------------------------------------------------------------
            */
            function getFilteredTreeData() {
                const searchValue = normalizeValue(searchInput?.value);
                const directionValue = normalizeValue(directionSelect?.value);
                if (searchValue === '' && directionValue === '') {
                    return cloneTreeData(originalTreeData);
                }
                const baseNode = searchValue === '' ? originalTreeData : findAssociateNode(originalTreeData,
                    searchValue);
                return buildTeamTree(baseNode, directionValue);
            }
            /*
            |--------------------------------------------------------------------------
            | Count visible associates excluding Root
            |--------------------------------------------------------------------------
            */
            function countVisibleAssociates(treeData) {
                if (!treeData) {
                    return 0;
                }
                let count = 0;

                function countNodes(node, isRoot = false) {
                    if (!isRoot && node.side !== 'root') {
                        count++;
                    }
                    (node.children || []).forEach(function(child) {
                        countNodes(child, false);
                    });
                }
                countNodes(treeData, true);
                return count;
            }
            /*
            |--------------------------------------------------------------------------
            | Filter status
            |--------------------------------------------------------------------------
            */
            function updateFilterStatus(treeData) {
                const searchValue = String(searchInput?.value || '').trim();
                const directionValue = String(directionSelect?.value || '').trim();
                const count = countVisibleAssociates(treeData);
                const rootName = treeData?.name || config.rootAssociateName || 'Associate';
                let titleText = `Associate Team - ${rootName}`;
                if (directionValue === 'left') {
                    titleText = `Left Associate Team - ${rootName}`;
                }
                if (directionValue === 'right') {
                    titleText = `Right Associate Team - ${rootName}`;
                }
                if (treeChartTitle) {
                    treeChartTitle.textContent = titleText;
                }
                if (visibleTreeCount) {
                    visibleTreeCount.textContent = count;
                }
                if (!filterStatus) {
                    return;
                }
                if (searchValue === '' && directionValue === '') {
                    filterStatus.textContent =
                        `${titleText}. Drag to move the chart and hover over a node for details.`;
                    if (downloadFilterText) {
                        downloadFilterText.textContent = titleText;
                    }
                    return;
                }
                const appliedFilters = [];
                if (searchValue !== '') {
                    appliedFilters.push(`Associate: ${rootName}`);
                }
                if (directionValue === 'left') {
                    appliedFilters.push('Left Team');
                }
                if (directionValue === 'right') {
                    appliedFilters.push('Right Team');
                }
                filterStatus.textContent =
                    `${titleText}. ${count} associates found - ${appliedFilters.join(' | ')}.`;
                if (downloadFilterText) {
                    downloadFilterText.textContent = titleText;
                }
            }
            /*
            |--------------------------------------------------------------------------
            | Clear SVG
            |--------------------------------------------------------------------------
            */
            function clearRenderedTree() {
                tooltip.style.display = 'none';
                while (svgElement.firstChild) {
                    svgElement.removeChild(svgElement.firstChild);
                }
                svgElement.removeAttribute('width');
                svgElement.removeAttribute('height');
                svgElement.removeAttribute('viewBox');
            }
            /*
            |--------------------------------------------------------------------------
            | Move complete subtree
            |--------------------------------------------------------------------------
            */
            function moveSubtree(node, difference) {
                node.each(function(descendant) {
                    descendant.x += difference;
                });
            }
            /*
            |--------------------------------------------------------------------------
            | Render associate tree
            |--------------------------------------------------------------------------
            */
            function renderAssociateTree(treeData) {
                clearRenderedTree();
                if (!treeData) {
                    currentSvgWidth = 0;
                    currentSvgHeight = 0;
                    updateFilterStatus(null);
                    return;
                }
                const root = d3.hierarchy(treeData);
                const treeLayout = d3.tree().nodeSize([
                    horizontalGap,
                    verticalGap,
                ]).separation(function(nodeA, nodeB) {
                    return nodeA.parent === nodeB.parent ? 1.12 : 1.35;
                });
                treeLayout(root);
                /*
                |--------------------------------------------------------------------------
                | Single child position
                |--------------------------------------------------------------------------
                */
                root.eachBefore(function(parentNode) {
                    if (!parentNode.children || parentNode.children.length !== 1) {
                        return;
                    }
                    const childNode = parentNode.children[0];
                    const requiredX = childNode.data.side === 'right' ? parentNode.x + singleChildOffset :
                        parentNode.x - singleChildOffset;
                    moveSubtree(childNode, requiredX - childNode.x);
                });
                /*
                |--------------------------------------------------------------------------
                | Prevent overlap
                |--------------------------------------------------------------------------
                */
                const nodesByDepth = d3.group(root.descendants(), function(node) {
                    return node.depth;
                });
                const minimumNodeDistance = nodeRadius * 2 + 34;
                nodesByDepth.forEach(function(levelNodes) {
                    levelNodes.sort(function(nodeA, nodeB) {
                        return nodeA.x - nodeB.x;
                    });
                    for (let index = 1; index < levelNodes.length; index++) {
                        const previousNode = levelNodes[index - 1];
                        const currentNode = levelNodes[index];
                        const currentDistance = currentNode.x - previousNode.x;
                        if (currentDistance >= minimumNodeDistance) {
                            continue;
                        }
                        moveSubtree(currentNode, minimumNodeDistance - currentDistance);
                    }
                });
                const allNodes = root.descendants();
                const minX = d3.min(allNodes, function(node) {
                    const radius = node.depth === 0 ? rootRadius : nodeRadius;
                    return node.x - radius;
                });
                const maxX = d3.max(allNodes, function(node) {
                    const radius = node.depth === 0 ? rootRadius : nodeRadius;
                    return node.x + radius;
                });
                const maximumY = d3.max(allNodes, function(node) {
                    const radius = node.depth === 0 ? rootRadius : nodeRadius;
                    return node.y + radius;
                });
                const svgWidth = Math.ceil(maxX - minX + outerPaddingX * 2);
                const svgHeight = Math.ceil(maximumY + outerPaddingTop + outerPaddingBottom);
                currentSvgWidth = svgWidth;
                currentSvgHeight = svgHeight;
                const xOffset = outerPaddingX - minX;
                const yOffset = outerPaddingTop;
                const svg = d3.select(svgElement).attr('width', svgWidth).attr('height', svgHeight).attr('viewBox',
                    `0 0 ${svgWidth} ${svgHeight}`);
                const chart = svg.append('g').attr('transform', `translate(${xOffset}, ${yOffset})`);
                /*
                |--------------------------------------------------------------------------
                | Lines
                |--------------------------------------------------------------------------
                */
                chart.append('g').attr('class', 'tree-links-layer').selectAll('path').data(root.links()).join(
                        'path')
                    .attr('class', 'tree-link').attr('d', function(link) {
                        const sourceRadius = link.source.depth === 0 ? rootRadius : nodeRadius;
                        const sourceX = link.source.x;
                        const sourceY = link.source.y + sourceRadius;
                        const targetX = link.target.x;
                        const targetY = link.target.y - nodeRadius;
                        const middleY = Math.round(sourceY + (targetY - sourceY) / 2);
                        return [`M ${sourceX} ${sourceY}`, `V ${middleY}`, `H ${targetX}`, `V ${targetY}`, ]
                            .join(
                                ' ');
                    });
                /*
                |--------------------------------------------------------------------------
                | Nodes
                |--------------------------------------------------------------------------
                */
                const nodes = chart.append('g').attr('class', 'tree-nodes-layer').selectAll('g').data(allNodes)
                    .join(
                        'g').attr('class', function(node) {
                        return ['svg-tree-node',
                            node.data.side || 'left',
                        ].join(' ');
                    }).attr('transform', function(node) {
                        return (`translate(${node.x}, ${node.y})`);
                    });
                nodes.append('circle').attr('class', 'svg-tree-node-circle').attr('r', function(node) {
                    return node.depth === 0 ? rootRadius : nodeRadius;
                });
                nodes.append('circle').attr('class', 'svg-avatar-circle').attr('cy', -22).attr('r', function(node) {
                    return node.depth === 0 ? 25 : 22;
                });
                nodes.append('text').attr('class', 'svg-avatar-text').attr('y', -22).text(function(node) {
                    return node.data.initial;
                });
                nodes.append('text').attr('class', 'svg-node-id').attr('y', 15).text(function(node) {
                    return node.data.associate_id;
                });
                nodes.append('text').attr('class', 'svg-node-name').attr('y', 31).text(function(node) {
                    const name = String(node.data.name || '');
                    return name.length > 15 ? name.substring(0, 15) + '...' : name;
                });
                nodes.append('rect').attr('class', function(node) {
                    return ['svg-direction-badge',
                        node.data.side,
                    ].join(' ');
                }).attr('x', -24).attr('y', 41).attr('width', 48).attr('height', 18).attr('rx', 9);
                nodes.append('text').attr('class', function(node) {
                    return ['svg-direction-text',
                        node.data.side,
                    ].join(' ');
                }).attr('y', 50).text(function(node) {
                    if (node.data.side === 'root') {
                        return 'Root';
                    }
                    return node.data.side === 'right' ? 'Right' : 'Left';
                });
                /*
                |--------------------------------------------------------------------------
                | Tooltip events
                |--------------------------------------------------------------------------
                */
                tooltipNodes = nodes;
                bindTooltipEvents(nodes);
                updateFilterStatus(treeData);
                /*
                 * Re-center after every filter.
                 */
                requestAnimationFrame(function() {
                    scrollArea.scrollLeft = Math.max(0,
                        (scrollArea.scrollWidth - scrollArea.clientWidth) / 2);
                    scrollArea.scrollTop = 0;
                });
            }
            /*
            |--------------------------------------------------------------------------
            | Tooltip helpers
            |--------------------------------------------------------------------------
            */
            function escapeHtml(value) {
                const temporaryElement = document.createElement('div');
                temporaryElement.textContent = String(value ?? '-');
                return temporaryElement.innerHTML;
            }

            function formatMoney(value) {
                return 'Rs. ' + Number(value || 0).toLocaleString('en-IN', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2,
                });
            }

            function formatArea(value) {
                return Number(value || 0).toLocaleString('en-IN', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2,
                }) + ' Sqft';
            }

            function capitalize(value) {
                const text = String(value || '-');
                return (text.charAt(0).toUpperCase() + text.slice(1));
            }

            function tooltipRow(label, value) {
                return `
                    <div class="compact-tooltip-row">
                        <span>${escapeHtml(label)}</span>
                        <strong>${escapeHtml(value)}</strong>
                    </div>
                `;
            }

            function moveTooltip(event) {
                const tooltipWidth = 270;
                const tooltipHeight = tooltip.offsetHeight || 350;
                let left = event.clientX + 16;
                let top = event.clientY + 16;
                if (left + tooltipWidth > window.innerWidth - 15) {
                    left = event.clientX - tooltipWidth - 16;
                }
                if (top + tooltipHeight > window.innerHeight - 15) {
                    top = window.innerHeight - tooltipHeight - 15;
                }
                tooltip.style.left = `${Math.max(15, left)}px`;
                tooltip.style.top = `${Math.max(15, top)}px`;
            }

            function bindTooltipEvents(nodes) {
                nodes.on('mouseenter', function(event, node) {
                    tooltip.innerHTML = `
                                <div class="compact-tooltip-header">
                                    <div class="compact-tooltip-avatar">
                                        ${escapeHtml(node.data.initial)}
                                    </div>

                                    <div>
                                        <div class="compact-tooltip-name">
                                            ${escapeHtml(node.data.name)}
                                        </div>

                                        <div class="compact-tooltip-id">
                                            ${escapeHtml(node.data.associate_id)}
                                        </div>
                                    </div>
                                </div>

                                <div class="compact-tooltip-body">
                                    ${tooltipRow(
                                        'Sponsor ID',
                                        node.data.sponsor_id
                                    )}

                                    ${tooltipRow(
                                        'Under Place',
                                        node.data.under_place_id
                                    )}

                                    ${tooltipRow(
                                        'Direction',
                                        capitalize(node.data.side)
                                    )}

                                    ${tooltipRow(
                                        'Direct Team',
                                        node.data.direct_count
                                    )}

                                    ${tooltipRow(
                                        'Downline',
                                        node.data.downline_count
                                    )}

                                    ${tooltipRow(
                                        'Left Team Business',
                                        formatMoney(node.data.left_team_business)
                                    )}

                                    ${tooltipRow(
                                        'Right Team Business',
                                        formatMoney(node.data.right_team_business)
                                    )}

                                    ${tooltipRow(
                                        'Left Team Area',
                                        formatArea(node.data.left_team_area)
                                    )}

                                    ${tooltipRow(
                                        'Right Team Area',
                                        formatArea(node.data.right_team_area)
                                    )}

                                    ${tooltipRow(
                                        'Self Business',
                                        formatMoney(node.data.self_business)
                                    )}

                                    ${tooltipRow(
                                        'Team Business',
                                        formatMoney(node.data.team_business)
                                    )}

                                    ${tooltipRow(
                                        'Total Business',
                                        formatMoney(node.data.total_business)
                                    )}

                                    ${tooltipRow(
                                        'Total Area',
                                        formatArea(node.data.total_area)
                                    )}

                                    ${tooltipRow(
                                        'Mobile',
                                        node.data.mobile
                                    )}

                                    ${tooltipRow(
                                        'Joining Date',
                                        node.data.joining_date
                                    )}
                                </div>
                            `;
                    tooltip.style.display = 'block';
                    moveTooltip(event);
                }).on('mousemove', function(event) {
                    moveTooltip(event);
                }).on('mouseleave', function() {
                    tooltip.style.display = 'none';
                });
            }
            /*
            |--------------------------------------------------------------------------
            | Apply filter
            |--------------------------------------------------------------------------
            */
            function applyRealtimeTreeFilter() {
                window.clearTimeout(searchTimer);
                window.clearTimeout(filterTimer);

                const requestId = ++filterRequestId;

                filterLoader?.classList.remove('d-none');

                filterTimer = window.setTimeout(function() {
                    if (requestId !== filterRequestId) {
                        return;
                    }

                    const filteredTreeData = getFilteredTreeData();

                    currentTreeData = filteredTreeData ?
                        cloneTreeData(filteredTreeData) :
                        null;

                    renderAssociateTree(currentTreeData);

                    filterLoader?.classList.add('d-none');

                    filterTimer = null;
                }, 160);
            }
            /*
            |--------------------------------------------------------------------------
            | Real-time Associate ID/Name search
            |--------------------------------------------------------------------------
            */
            let searchTimer = null;
            let filterTimer = null;
            let filterRequestId = 0;
            searchInput?.addEventListener('input', function() {
                window.clearTimeout(searchTimer);
                window.clearTimeout(filterTimer);

                const requestId = ++filterRequestId;

                searchTimer = window.setTimeout(function() {
                    if (requestId !== filterRequestId) {
                        return;
                    }

                    applyRealtimeTreeFilter();
                }, 250);
            });
            /*
            |--------------------------------------------------------------------------
            | Direction change
            |--------------------------------------------------------------------------
            */
            directionSelect?.addEventListener('change', function() {
                window.clearTimeout(searchTimer);
                window.clearTimeout(filterTimer);

                filterRequestId++;

                applyRealtimeTreeFilter();
            });
            /*
            |--------------------------------------------------------------------------
            | Show button
            |--------------------------------------------------------------------------
            */
            applyFilterButton?.addEventListener('click', function() {
                window.clearTimeout(searchTimer);
                applyRealtimeTreeFilter();
            });
            /*
            |--------------------------------------------------------------------------
            | Enter key
            |--------------------------------------------------------------------------
            */
            searchInput?.addEventListener('keydown', function(event) {
                if (event.key !== 'Enter') {
                    return;
                }
                event.preventDefault();
                window.clearTimeout(searchTimer);
                applyRealtimeTreeFilter();
            });
            /*
            |--------------------------------------------------------------------------
            | Reset
            |--------------------------------------------------------------------------
            */


            resetFilterButton?.addEventListener('click', function(event) {
                event.preventDefault();

                window.clearTimeout(searchTimer);

                if (searchInput) {
                    searchInput.value = '';
                }

                if (directionSelect) {
                    directionSelect.value = '';
                }

                filterLoader?.classList.add('d-none');

                currentTreeData = cloneTreeData(originalTreeData);

                renderAssociateTree(currentTreeData);
            });
            
            /*
            |--------------------------------------------------------------------------
            | Drag scrolling
            |--------------------------------------------------------------------------
            */
            let isDragging = false;
            let dragStartX = 0;
            let dragStartY = 0;
            let originalScrollLeft = 0;
            let originalScrollTop = 0;
            scrollArea.addEventListener('mousedown', function(event) {
                if (event.target.closest('.svg-tree-node')) {
                    return;
                }
                isDragging = true;
                dragStartX = event.pageX;
                dragStartY = event.pageY;
                originalScrollLeft = scrollArea.scrollLeft;
                originalScrollTop = scrollArea.scrollTop;
                scrollArea.classList.add('is-dragging');
            });
            window.addEventListener('mousemove', function(event) {
                if (!isDragging) {
                    return;
                }
                event.preventDefault();
                scrollArea.scrollLeft = originalScrollLeft - (event.pageX - dragStartX);
                scrollArea.scrollTop = originalScrollTop - (event.pageY - dragStartY);
            });
            window.addEventListener('mouseup', function() {
                isDragging = false;
                scrollArea.classList.remove('is-dragging');
            });
            /*
            |--------------------------------------------------------------------------
            | Download currently filtered tree
            |--------------------------------------------------------------------------
            */
            const downloadButton = document.getElementById('downloadTree');
            downloadButton?.addEventListener('click', async function() {
                const spinner = document.getElementById('downloadSpinner');
                const buttonText = document.getElementById('downloadButtonText');
                const downloadHeading = document.getElementById('treeDownloadHeading');
                if (currentSvgWidth <= 0 || currentSvgHeight <= 0) {
                    alert('Download ke liye koi associate nahi mila.');
                    return;
                }
                downloadButton.disabled = true;
                spinner?.classList.remove('d-none');
                if (buttonText) {
                    buttonText.textContent = 'Preparing...';
                }
                tooltip.style.display = 'none';
                exportContainer.classList.add('download-mode');
                try {
                    await document.fonts.ready;
                    await new Promise(function(resolve) {
                        requestAnimationFrame(function() {
                            requestAnimationFrame(resolve);
                        });
                    });
                    const exportWidth = Math.ceil(currentSvgWidth + 60);
                    if (downloadHeading) {
                        downloadHeading.style.width = `${currentSvgWidth}px`;
                    }
                    exportContainer.style.width = `${exportWidth}px`;
                    const exportHeight = Math.ceil(exportContainer.scrollHeight);
                    const imageData = await htmlToImage.toPng(exportContainer, {
                        cacheBust: true,
                        pixelRatio: 2,
                        backgroundColor: '#f8fafc',
                        width: exportWidth,
                        height: exportHeight,
                        canvasWidth: exportWidth * 2,
                        canvasHeight: exportHeight * 2,
                        style: {
                            width: `${exportWidth}px`,
                            height: `${exportHeight}px`,
                            minWidth: '0',
                            maxWidth: 'none',
                            overflow: 'visible',
                        },
                    });
                    const safeAssociateId = String(config.rootAssociateId || 'root').replace(
                        /[^a-zA-Z0-9-_]/g, '-');
                    const direction = normalizeValue(directionSelect?.value);
                    const filterSuffix = direction ? `-${direction}` : '';
                    const currentDate = new Date().toISOString().slice(0, 10);
                    const downloadLink = document.createElement('a');
                    downloadLink.download =
                        `associate-tree-${safeAssociateId}${filterSuffix}-${currentDate}.png`;
                    downloadLink.href = imageData;
                    document.body.appendChild(downloadLink);
                    downloadLink.click();
                    downloadLink.remove();
                } catch (error) {
                    console.error('Tree download error:', error);
                    alert('Tree image download nahi ho payi. Page refresh karke dobara try karein.');
                } finally {
                    exportContainer.classList.remove('download-mode');
                    exportContainer.style.width = '';
                    if (downloadHeading) {
                        downloadHeading.style.width = '';
                    }
                    downloadButton.disabled = false;
                    spinner?.classList.add('d-none');
                    if (buttonText) {
                        buttonText.textContent = 'Download Tree';
                    }
                }
            });
            /*
            |--------------------------------------------------------------------------
            | Initial render
            |--------------------------------------------------------------------------
            */
            renderAssociateTree(currentTreeData);
        });
    </script>
@endpush
